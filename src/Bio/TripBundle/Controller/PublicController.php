<?php

namespace Bio\TripBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\TripBundle\Entity\Trip;
use Bio\TripBundle\Entity\Evaluation;
use Bio\TripBundle\Entity\Response;

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

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());

        if ($global->getOpening() > new \DateTime()) {
            $flash->set('failure', 'Field trip signups start '.$global->getOpening()->format('F j, Y \a\t g:i a').'.');
        } else {
        	if ($request->query->has('logout')) {
        		$session->invalidate();
        	}

        	if ($session->has('studentID')) {
        		return $this->tripAction($request, $session->get('studentID'), $global);
        	}
        }

    	return $this->signAction($request, $global);
    }

    private function signAction(Request $request, $global) {
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

    private function tripAction(Request $request, $id, $global) {
    	$db = new Database($this, 'BioTripBundle:Trip');
    	$trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);
        $db = new Database($this, 'BioStudentBundle:Student');
        $student = $db->findOne(array('id' => $id));

        if (!$student) {
            $request->getSession()->invalidate();
            $request->getSession()->getFlashBag()->set('failure', 'Not signed in.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery('
                SELECT t
                FROM BioTripBundle:Trip t
                WHERE :student MEMBER OF t.students
            ')->setParameter('student', $student);

        $yourTrips = $query->getResult();

    	return $this->render('BioTripBundle:Public:browse.html.twig', array('trips' => $trips, 'current' => $yourTrips, 'global' => $global, 'title' => 'Sign Up'));
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
    			if (count($trip->getStudents()) >= $trip->getMax()) {
    				$request->getSession()->getFlashBag()->set('failure', 'Trip is full.');
    			} else {
	    			$trip->addStudent($student);
	    			try {
	    				$db->close();
	    				$request->getSession()->getFlashBag()->set('success', 'Joined trip.');
	    			} catch (BioException $e) {
	    				$request->getSession()->getFlashBag()->set('failure', 'You cannot sign up for any more trips.');
	    			}
	    		}
    		}
    	}
    	return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/leave", name="leave_trip")
     */
    public function leaveAction(Request $request) {
    	if (!$request->getSession()->has('studentID')) {
    		$request->getSession()->getFlashBag()->set('failure', 'Not signed in.');
    	} else if (!$request->query->has('id')) {
    		$request->getSession()->getFlashBag()->set('failure', 'No trip specified.');
    	} else {
    		$studentID = $request->getSession()->get('studentID');
    		$tripID = $request->query->get('id');

    		$db = new Database($this, 'BioStudentBundle:Student');
    		$student = $db->findOne(array('id' => $studentID));

    		$db = new Database($this, 'BioTripBundle:Trip');
    		$trip = $db->findOne(array('id' => $tripID));

    		if (!$student || !$trip) {
    			$request->getSession()->getFlashBag()->set('failure', 'No trip found.');
    		} else {
	    		$trip->removeStudent($student);
	    		$db->close();
	    		$request->getSession()->getFlashBag()->set('success', 'Left trip.');
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

    		if ($trip){
    			return array('trip' => $trip, 'title' => $trip->getTitle());
    		}
    		$request->getSession()->getFlashBag()->set('failure', 'Could not find that trip.');
    	}

    	return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/eval/{tripID}/{tripTitle}", name="eval_trip")
     * @Template()
     */
    public function evalAction(Request $request, $tripID) {
    	if (!$request->getSession()->has('studentID')) {
    		$request->getSession()->getFlashBag()->set('failure', 'Not signed in.');
    		return $this->redirect($this->generateUrl('trip_entrance'));
    	} else {
    		$db = new Database($this, 'BioStudentBundle:Student');
    		$student = $db->findOne(array('id' => $request->getSession()->get('studentID')));

    		$db = new Database($this, 'BioTripBundle:Trip');
    		$trip = $db->findOne(array('id' => $tripID));

    		if (!$student || !$trip) {
    			$request->getSession()->getFlashBag()->set('failure', 'Could find trip or student.');
    			return $this->redirect($this->generateUrl('trip_entrance'));
    		}

            $db = new Database($this, 'BioTripBundle:TripGlobal');
            $global = $db->findOne(array());

    		$formBuilder = $this->createFormBuilder();
    		foreach($global->getEvalQuestions() as $question) {
                if ($question->getType() === 'multiple') {
                    $formBuilder->add($question->getId(), 'choice', array('label' => $question->getData()[1], 'choices' => range(0,$question->getData()[2]), 'expanded' => true, 'attr' => array('class' => 'horizontal')));
                } else if ($question->getType() === 'response') {
                    $formBuilder->add($question->getId(), 'textarea', array('label' => $question->getData()[0]));
                }
            }
            $formBuilder->add('submit', 'submit');
            $form = $formBuilder->getForm();


    		if ($request->getMethod() === "POST") {
    			$form->handleRequest($request);
    			if ($form->isValid()) {

                    $eval = new Evaluation();
                    $eval->setTimestamp(new \Datetime())
                        ->setStudent($student)
                        ->setTrip($trip);

                    $db = new Database($this, 'BioTripBundle:EvalQuestion');
                    foreach (array_keys($form->getData()) as $key) {
                        $question = $db->findOne(array('id' => $key));

                        if (!$question) {
                            // throw error
                        }

                        $response = new Response();
                        $response->setAnswer($form->getData()[$key])
                            ->setEvalQuestion($question);
                        $eval->addResponse($response);
                        $db->add($response);
                    }
                    $db->add($eval);
                    $trip->addEval($eval);


    				try {
    					$db->close();
    					$request->getSession()->getFlashBag()->set('success', 'Evaluation saved.');
    				} catch (BioException $e) {
    					$request->getSession()->getFlashBag()->set('failure', 'You can only write an evaluation once.');
    				}

    				return $this->redirect($this->generateUrl('trip_entrance'));
    			}
    		}

    		return array('form' => $form->createView(), 'title' => 'Trip Evaluation');
    	}
    }

}