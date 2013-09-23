<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\StudentBundle\Entity\Student;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

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
     * @Route("/../../clicker", name="register_clicker")
     * @Template()
     */
    public function registerAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();
    	$form = $this->createFormBuilder()
    		->add('cid', 'text', array(
                'label' => "Clicker ID:",
                'constraints' => array(
                    new Assert\Regex("/^[0-9A-Fa-f]{6}$/"),
                    new Assert\NotBlank()
                    ),
                'attr' => array(
                    'pattern' => '[0-9A-Fa-f]{6}',
                    'title' => '6 digit clicker ID'
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
     * @Template()
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
                $student->getEmail()."\t".
                $clicker->getStudent()->getSid();
        }

        $response = $this->render('BioClickerBundle:Default:download.html.twig', array(
            'test' => implode("\n", $responseText)
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
        $flash = $request->getSession()->getFlashBag();
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
		        $flash->set('success', 'All clicker registrations cleared.');
                return $this->redirect($this->generateUrl('clear_list'));
    		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Clear Registrations');
    }
}
