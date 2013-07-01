<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\StudentBundle\Entity\Student;

/**
 * @Route("/clicker")
 * @Template()
 */
class DefaultController extends Controller {
    /**
     * @Route("/", name="register_clicker")
     * @Template()
     */
    public function registerAction(Request $request) {
    	$clicker = new Clicker();
    	$form = $this->createFormBuilder($clicker)
    		->add('cid', 'text', array('label' => "Clicker ID:"))
    		->add('sid', 'text', array('label' => "Student ID:"))
    		->add('fName', 'text', array('label' => "First Name:", 'mapped' => false))
    		->add('lName', 'text', array('label' => "Last Name:", 'mapped' => false))
    		->add('Register', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		
    		if ($form->isValid()){

	    		$em = $this->getDoctrine()->getManager();
	    		$repo = $em->getRepository('BioStudentBundle:Student');
	    		$student = $repo->findOneBy(array('sid' => $form->get('sid')->getData(), 'fName' => $form->get('fName')->getData(), 'lName' => $form->get('lName')->getData()));

	    		if ($student) {
	    			$clicker = new Clicker();
	    			$clicker->setCid($form->get('cid')->getData());
	    			$clicker->setSid($student->getSid());
	    			$em->persist($clicker);
	    			try {
	    				$em->flush();
	    				$request->getSession()->getFlashBag()->set('success', 'Clicker registered!');
	    			} catch (\Doctrine\DBAL\DBALException $e) {
	    				$request->getSession()->getFlashBag()->set('failure', 'That clicker has already been registered.');
	    			}
	    		} else {
	    			$request->getSession()->getFlashBag()->set('failure', 'We could not find that student.');
	    		}
	    	} else {
	    		$request->getSession()->getFlashBag()->set('failure', 'Invalid Clicker or Student ID.');
	    	}
    	}

        return array('form' => $form->createView(), 'title' => "Register Clicker");
    }
}
