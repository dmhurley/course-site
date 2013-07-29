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
    				return $this->redirect($this->generateUrl('trip_entrance'));
    			} else {
    				$request->getSession()->getFlashBag()->set('failure', 'Could not find a student with that last name and student ID.');
    			}
    		}
    	}

    	return $this->render('BioExamBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Log In'));
    }

    private function tripAction(Request $request, $id) {
    	$db = new Database($this, 'BioTripBundle:Trip');
    	$trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);
    	$trip = null;
    	foreach ($trips as $t) {
    		foreach($t->getStudents() as $student) {
    			if ($student->getId() === $id) {
    				$trip = $t;
    				break 2;
    			}
    		}
    	}

    	return $this->render('BioTripBundle:Public:browse.html.twig', array('trips' => $trips, 'current' => $trip, 'title' => 'Sign Up'));
    }

    /**
     * @Route("/join", name="join_trip")
     */
    public function joinAction(Request $request) {
    	if (!$request->getSession()->has('studentID')) {
    		$request->getSession()->getFlashBag()->set('failure', 'Not signed in.');
    	} else if (!$request->query->has('id')) {
    		$request->getSession()->getFlashBag()->set('failure', 'No trip specified.');
    	} else {
    		$tripID = $request->query->get('id');
    		$studentID = $request->getSession()->get('studentID');

    		$db = new Database($this, 'BioStudentBundle:Student');
    		$student = $db->findOne(array('id' => $studentID));

    		$db = new Database($this, 'BioTripBundle:Trip');
    		$trip = $db->findOne(array('id' => $tripID));

    		if (!$student || !$trip) {
    			$request->getSession()->getFlashBag()->set('failure', 'No trip found.');
    		} else {
    			$trip->addStudent($student);
    			$db->close();
    		}
    	}
    	return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/view", name="view_trip")
     * @Template()
     */
    public function viewAction(Request $request) {
    	if ($request->query->has('id')) {
    		$id = $request->query->get('id');

    		$db = new Database($this, 'BioTripBundle:Trip');
    		$trip = $db->findOne(array('id' => $id));

    		return array('trip' => $trip, 'title' => $trip->getTitle());
    	}

    	if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('trip_entrance'));
        }
    }

}