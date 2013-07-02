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
     * @Route("/register", name="register_clicker")
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
	    		$studentRepo = $em->getRepository('BioStudentBundle:Student');
	    		$student = $studentRepo->findOneBy(array('sid' => $form->get('sid')->getData(), 'fName' => $form->get('fName')->getData(), 'lName' => $form->get('lName')->getData()));

	    		if ($student) {		// if student exists
					$clickerRepo = $em->getRepository('BioClickerBundle:Clicker');
					$clicker = $clickerRepo->findOneBy(array('sid' => $student->getSid()));
					if (!$clicker){
						$clicker = new Clicker();
						$em->persist($clicker);
						$request->getSession()->getFlashBag()->set('success', "Clicker registered.");
					} else { 
						$request->getSession()->getFlashBag()->set('success', "Clicker changed.");
					}
					$clicker->setCid($form->get('cid')->getData());
					$clicker->setSid($student->getSid());

					try {
						$em->flush();
					} catch (\Doctrine\DBAL\DBALException $e) {
						$request->getSession()->getFlashBag()->set('failure', "Someone else is already registered to that clicker.");
						$request->getSession()->getFlashBag()->get('success');
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

    /**
     * @Template()
     * @Route("/download")
     */
    public function downloadAction(Request $request) {
    	$this->update();
    	$tring = "Last Name\tFirst Name\tclicker ID\tStudent ID\n";
    	$clickerRepo = $this->getDoctrine()->getRepository('BioClickerBundle:Clicker');
    	$studentRepo = $this->getDoctrine()->getRepository('BioStudentBundle:Student');

    	$clickers = $clickerRepo->findBy(array());
    	foreach ($clickers as $clicker) {
    		$student = $studentRepo->findOneBySid($clicker->getSid());
    		$tring.= $student->getLName()."\t";
    		$tring.= $student->getFName()."\t";
    		$tring.= $clicker->getCid()."\t";
    		$tring.= $clicker->getSid()."\n";
    	}
    	header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=clickerReg.xls");
		header("Content-Type: application/octet-stream; "); 
		header("Content-Transfer-Encoding: binary");

	    return array('test' => $tring);
    }

    public function update() {
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    		'DELETE FROM BioClickerBundle:Clicker c WHERE c.sid NOT IN (SELECT d.sid FROM BioStudentBundle:Student d)'
    	);
    	$query->getResult();
    }
}
