<?php

namespace Bio\TripBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\TripBundle\Entity\Trip;

/** 
 * @Route("/trip")
 */
class PublicController extends Controller
{
    /**
     * @Route("", name="trip_entrance")
     * @Template()
     */
    public function indexAction(Request $request) {
    	$session = $request->getSession();
    	$flash = $session->getFlashBag();

    	if ($request->query->has('logout')) {
    		$session->invalidate();
    	}

    	if ($session->has('studentID')) {
    		return $this->tripAction($request, $session->get('studentID'));
    	}

    	return $this->signAction($request);
    }

    private function signAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false))
    		->add('lName', 'text', array('label' => 'Last Name:', 'mapped' => false))
    		->add('sign in', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->has('sid') && $form->has('lName')) {
    			$sid = $form->get('sid')->getData();
    			$lName = $form->get('lName')->getData();

    			$db = new Database($this, 'BioStudentBundle:Student');

    			$student = $db->findOne(array('sid' => $sid, 'lName' => $lName));
    			if ($student) {
    				$request->getSession()->set('studentID', $student->getId());
    			} else {
    				$request->getSession()->getFlashBag()->set('failure', 'Could not find a student with that last name and student ID.');
    			}
    		}
    	}

    	return $this->render('BioExamBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Log In'));
    }

    private function tripAction(Request $request, $id) {
    	$db = new Database($this, 'BioTripBundle:Trip');
    	// find field trip
    	if (false) {

    	} else {
    		$trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);
    		return $this->render('BioTripBundle:Public:browse.html.twig', array('trips' => $trips, 'title' => 'Sign Up'));
    	}
    }

}