<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

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

        $students = $this->getDoctrine()
                         ->getManager()
                         ->getRepository('BioClickerBundle:Clicker')
                         ->getUnregisteredStudents();

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

        // get data
        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('BioClickerBundle:Clicker');

        $clickers = $repo->findAll();
        $unregistered = $repo->getUnregisteredStudents();

        // build response
        $response = $this->render('BioClickerBundle:Admin:download.xls.twig', array(
            'clickers' => $clickers,
            'unregistered' => $unregistered
        ));

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

        // this form is a simple checkbox that `must` be
        // checked in order for the form to be valid
    	$form = $this->createFormBuilder()
    		->add('confirmation', 'checkbox', array(
                'constraints' => new Assert\True(
                    array('message' => 'You must confirm.')
                    )
                )
            )
    		->add('clear', 'submit', array('label' => 'Clear Clickers'))
    		->getForm();

        // only if the form is submitted and is valid
        // do we try to clear the clicker registrations
    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$this->getDoctrine()
                     ->getManager()
                     ->getRepository('BioClickerBundle:Clicker')
                     ->removeAll();
                
		        $flash->set('success', 'All clicker registrations cleared.');
                return $this->redirect($this->generateUrl('clear_list'));
    		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Clear Registrations');
    }
}
