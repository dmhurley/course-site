<?php

namespace Bio\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\StudentBundle\Entity\Student;

/**
 * @Route("/student")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/add")
     * @Template()
     */
    public function addAction(Request $request)
    {
    	$entity = new Student();
    	$form = $this->createFormBuilder($entity)
    		->add('sid', 'integer', array('label' => "Student ID:"))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
    		->add('email', 'email', array('label' => "Email:"))
    		->add('Submit', 'submit')
    		->getForm();

    	$cloned = clone $form;

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {
    			$em = $this->getDoctrine()->getManager();
    			try {
	    			$em->persist($entity);
	    			$em->flush();
	    			$request->getSession()->getFlashBag()->set('success', 'Student added :)');
	    		} catch (\Doctrine\DBAL\DBALException $e) {
	    			$request->getSession()->getFlashBag()->set('failure', 'That ID or Email is already in the database :(');
	    		}
	    		$form = $cloned;
    		} else {
    			$request->getSession()->getFlashBag()->set('failure', 'There was an error. Please try again :(');
    		}
    	}
        return array('form' => $form->createView());
    }

    /**
     * @Route("/delete")
     * @Template()
     */
    public function deleteAction(Request $request) {
    	$form = $this->createFormBuilder(new Student())
    		->add('sid', 'integer', array('label' => "Student ID:"))
    		->getForm();

    	$cloned = clone $form;

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		$sid = $form->get('sid')->getData();
    		$em = $this->getDoctrine()->getManager();
    		$repo = $em->getRepository('BioStudentBundle:Student');

    		$entity = $repo->findOneBySid($sid);
    		if (!$entity) {
    			$request->getSession()->getFlashBag()->set('failure', "Student #".$sid." does not exist");
    		} else {
    			$em->remove($entity);
    			$em->flush();
    			$request->getSession()->getFlashBag()->set('success', "Student #".$sid." removed.");
    		}
    	}

    	return array('form' => $cloned->createView());
    }
}
