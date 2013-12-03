<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\ClickerBundle\Entity\ClickerGlobal;
use Bio\StudentBundle\Entity\Student;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\ClickerBundle\Form\ClickerGlobalType;

/**
 * @Route("/admin/clicker")
 */
class DefaultController extends Controller {
    /**
     * @Route("/", name="clicker_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Clickers');
    }

    /**
     * @Route("/manage", name="manage_clickers")
     * @Template()
     */
    public function manageAction(Request $request) {
        $form = $this->createForm(new ClickerGlobalType(), null, array(
                    'action' => $this->generateUrl('global_entity', array(
                            'bundle' => 'clicker',
                            'entityName' => 'clickerGlobal'
                        )
                    )
                )
            )
            ->add('save', 'submit');

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('
            SELECT s
            FROM BioStudentBundle:Student s
            WHERE NOT EXISTS (
                    SELECT c
                    FROM BioClickerBundle:Clicker c
                    WHERE s = c.student
                )
            ORDER BY s.lName ASC
            ');
        $students = $query->getResult();

        return array(
            'form' => $form->createView(),
            'students' => $students,
            'title' => 'Manage Clickers'
            );
    }

    /**
     * @Route("/../../clicker", name="register_clicker")
     * @Template()
     */
    public function registerAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();
    	$form = $this->createFormBuilder()
    		->add('cid', 'text', array(
                'label' => "Clicker ID:",
                'constraints' => array(
                    new Assert\Regex(array(
                        "pattern" => "/^[0-9A-Fa-f]{6}$/",
                        "message" => "6 digit clicker ID (0-9 A-F).")),
                    new Assert\NotBlank()
                    ),
                'attr' => array(
                    'pattern' => '[0-9A-Fa-f]{6}',
                    'title' => '6 digit clicker ID (0-9 A-F).'
                    )
                )
            )
    		->add('Register', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
            $clicker = new Clicker();
    		
    		if ($form->isValid()){
                $student = $this->get('security.context')->getToken()->getUser();
	    		
                $db = new Database($this, 'BioClickerBundle:ClickerGlobal');
                $global = $db->findOne(array());
                $db = new Database($this, 'BioInfoBundle:Info');
                $info = $db->findOne(array());

				$db = new Database($this, 'BioClickerBundle:Clicker');
                if ($dbClicker = $db->findOne(array('student' => $student))){
                    $flash->set('success', "Clicker ID changed to #".$form->get('cid')->getData());
                    $clicker = $dbClicker;
                } else {
                    $flash->set('success', "Clicker ID #".$form->get('cid')->getData()." registered.");
                    $db->add($clicker);
                    $clicker->setStudent($student);
                }
				$clicker->setCid($form->get('cid')->getData());

				try {
					$db->close();

                    if ($global->getStart() <= new \DateTime() && $global->getNotifications()) {
                        $message = \Swift_Message::newInstance()
                            ->setSubject(
                                'New Clicker Registration: '. $clicker->getCid().
                                ' - '.$student->getFName()." ".$student->getLName()
                                )
                            ->setFrom($this->container->getParameter('mailer_dev_address'))
                            ->setTo($info->getEmail())
                            ->setBody(
                                    $student->getFName().' '. $student->getLName() .
                                    ' registered clicker #'. $clicker->getCid() .
                                    ' at '.(new \DateTime())->format('Y-m-d H:i:s').'.'
                                    );
                        $this->container->get('mailer')->send($message);
                    }

                    return $this->redirect($this->generateUrl('register_clicker'));
				} catch (BioException $e) {
					$flash->set('failure', "Invalid form.");
                    $form->get('cid')->addError(new FormError('Someone else is already registered to that clicker.'));
					$flash->get('success'); // remove the successful flash message that was set earlier
				}

	    	} else {
	    		$flash->set('failure', 'Invalid form.');
	    	}
    	}

        return array('form' => $form->createView(), 'title' => "Register Clicker");
    }

    /**
     * @Route("/download", name="download_list")
     */
    public function downloadAction(Request $request) {
        $db = new Database($this, 'BioClickerBundle:Clicker');
        $clickers = $db->find(array(), array(), false);
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('
            SELECT s
            FROM BioStudentBundle:Student s
            WHERE NOT EXISTS (
                    SELECT c
                    FROM BioClickerBundle:Clicker c
                    WHERE s = c.student
                )
            ORDER BY s.lName ASC
            ');
        $students = $query->getResult();

    	$responseText = ["Last Name\tFirst Name\tclicker ID\tStudent ID"];
    	foreach ($clickers as $clicker) {
    		$responseText[] = $clicker->getStudent()->getLName()."\t".
    		    $clicker->getStudent()->getFName()."\t".
    		    $clicker->getCid()."\t".
    		    $clicker->getStudent()->getSid();
    	}
        $responseText[] = "\n\n";
        foreach ($students as $student) {
            $responseText[] = $student->getLName()."\t".
                $student->getFName()."\t".
                "\t".
                $student->getSid();
        }

        $response = $this->render('BioPublicBundle:Template:blank.html.twig', array(
            'text' => implode("\n", $responseText)
            )
        );
        $response->headers->set(
            "Content-Type", 'application/vnd.ms-excel'
            );

        $response->headers->set(
            'Content-Disposition', ('attachment; filename="clickerReg.xls"')
            );
        return $response;
    }

    /**
     * @Route("/clear", name="clear_list")
     * @Template()
     */
    public function clearAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('confirmation', 'checkbox', array(
                'constraints' => new Assert\True(
                    array('message' => 'You must confirm.')
                    )
                )
            )
    		->add('clear', 'submit', array('label' => 'Clear Clickers'))
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$db = new Database($this, 'BioClickerBundle:Clicker');
                $db->truncate();
                return $this->render('BioDataBundle:Crud:full.json.twig', array());
    		} else {
                 return $this->render('BioDataBundle:Crud:full.json.twig', array(
                    'error' => 'Invalid form.'
                    )
                 );
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Clear Registrations');
    }
}
