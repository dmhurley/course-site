<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\ClickerBundle\Form\ClickerGlobalType;
use Bio\DataBundle\Objects\Database;

/**
 * @Route("/admin/clicker")
 */
class AdminController extends Controller {
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
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioClickerBundle:ClickerGlobal');
        $global = $db->findOne(array());

        $form = $this->createForm(new ClickerGlobalType(), $global)
            ->add('save', 'submit');

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $db->close();
                $flash->set('success', 'Changes saved.');
            } else {
                $flash->set('failure', 'Changes not saved.');
            }
        }

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
