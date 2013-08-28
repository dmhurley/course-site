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

            return $this->tripAction($request, $session->get('studentID'), $global);
        }

    	return $this->forward('BioPublicBundle:Default:sign', array('request' => $request, 'redirect' => 'trip_entrance'));
    }

    private function tripAction(Request $request, $id, $global) {
    	$db = new Database($this, 'BioTripBundle:Trip');
    	$trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);

        $student = $this->get('security.context')->getToken()->getUser();

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
     * @Route("/join/{id}", name="join_trip")
     */
    public function joinAction(Request $request, Trip $trip = null) {
    	if (!$trip) {
    		$request->getSession()->getFlashBag()->set('failure', 'Trip could not be found.');
    	} else if ($trip->getStart() < new \DateTime()) {
            $request->getSession()->getFlashBag()->set('failure', 'Too late to join.');
        } else {
    		$student = $this->get('security.context')->getToken()->getUser();
            // TODO make sure trip hasn't passed!!!!!!
			if (count($trip->getStudents()) >= $trip->getMax()) {
				$request->getSession()->getFlashBag()->set('failure', 'Trip is full.');
			} else {
    			$trip->addStudent($student);
    			try {
                    $db = new Database($this, 'BioTripBundle:Trip');
    				$db->close();
    				$request->getSession()->getFlashBag()->set('success', 'Joined trip.');
    			} catch (BioException $e) {
    				$request->getSession()->getFlashBag()->set('failure', 'You cannot sign up for any more trips.');
    			}
    		}
    	}
    	return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/leave/{id}", name="leave_trip")
     */
    public function leaveAction(Request $request, Trip $trip = null) {
    	if (!$trip) {
    		$request->getSession()->getFlashBag()->set('failure', 'Trip not found.');
    	} else if ($trip->getStart() < new \DateTime()) {
            $request->getSession()->getFlashBag()->set('failure', 'Too late to leave.');
        } else {
    		$student = $this->get('security.context')->getToken()->getUser();
            // TODO make sure trip hasn't passed!!!!!!
    		$trip->removeStudent($student);
            try {
              $db = new Database($this, 'BioTripBundle:Trip');  
    		  $db->close();
    		  $request->getSession()->getFlashBag()->set('success', 'Left trip.');
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Error.');
            }
    	}


    	return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/view/{id}", name="view_trip")
     * @Template()
     */
    public function viewAction(Request $request, Trip $trip = null) {
		if ($trip){
			return array('trip' => $trip, 'title' => 'View Trip');
		}
		$request->getSession()->getFlashBag()->set('failure', 'Could not find that trip.');
        return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/eval/{id}/{tripTitle}", name="eval_trip")
     * @Template()
     */
    public function evalAction(Request $request, Trip $trip = null) {
        /****** GET STUFF FROM DATABASE ******/
        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array()); 
    
        $student = $this->get('security.context')->getToken()->getUser();

        $db = new Database($this, 'BioTripBundle:Evaluation');
        $eval = $db->findOne(array('trip' => $trip, 'student' => $student));

        /****** DOES STUDENT/TRIP EXISTS ******/
        if (!$trip) {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find trip.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        /****** IS TOO LATE TO EVALUATE? ******/
        if ($global->getClosing() < new \DateTime()) {
            $request->getSession()->getFlashBag()->set('failure', 'It is too late to submit evaluations.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        if ($eval) {
            $request->getSession()->getFlashBag()->set('failure', 'You have already submitted an evaluation.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        if ($request->getMethod() === "POST") {
            $areErrors = false;
            $validator = $this->get('validator');

            $eval = new Evaluation();
            $eval->setTimestamp(new \DateTime())
                ->setStudent($student)
                ->setTrip($trip);
            $db->add($eval);
            if (count($request->request->keys()) !== count($global->getEvalQuestions())) {
                $request->getSession()->getFlashBag()->set('failure', 'Error.');
            } else {

                foreach($request->request->keys() as $key) {
                    $question = $this->findObjectByFieldValue($key, $global->getEvalQuestions(), 'id');
                    if ($question) {
                        $response = new Response();
                        $response->setAnswer($request->request->get($key))
                            ->setEvalQuestion($question);
                        $db->add($response);
                        $eval->addResponse($response);
                    } else {
                        $areErrors = true;
                        $request->getSession()->getFlashBag()->set('failure', 'Invalid IDs.');
                        break;
                    }

                    $errors = $validator->validate($response);
                    if (count($errors) > 0){
                        $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                        $areErrors = true;
                        $question->errors = $errors;
                    }

                    if (!$areErrors) {
                        try {
                            $db->close();
                            $request->getSession()->getFlashBag()->set('success', 'Evaluation saved.');
                            return $this->redirect($this->generateUrl('trip_entrance'));
                        } catch (BioException $e) {
                            $request->getSession()->getFlashBag()->set('failure', 'Could not save evaluation.');
                        }
                    }
                }
            }
        }

        return array('global' => $global, 'title' => 'Trip Evaluations');
    }

    /**
     * @Route("/guide", name="tour_guide_entrance")
     * @Template()
     */
    public function guideAction(Request $request) {
        if ($request->getSession()->has('leaderEmail')) {
            $email = $request->getSession()->get('leaderEmail');

            $db = new Database($this, 'BioTripBundle:Trip');
            $trips = $db->find(array('email' => $email), array(), false);

            return array('trips' => $trips, 'title' => 'Your Trips');
        } else {
            return $this->signIn($request);
        }
    }
    private function signIn(Request $request) {
        $form = $this->createFormBuilder()
            ->add('email', 'text', array('label' => 'Email:'))
            ->add('password', 'password', array('label' => 'Password:'))
            ->add('login', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $db = new Database($this, 'BioTripBundle:TripGlobal');
                $global = $db->findOne(array());

                $db = new Database($this, 'BioTripBundle:Trip');
                $trips = $db->find(array('email' => $form->get('email')->getData()), array(), false);

                if (count($trips) > 0) {
                    if ($form->get('password')->getData() === $global->getGuidePass()) {
                        $request->getSession()->set('leaderEmail', $form->get('email')->getData());
                        return $this->redirect($this->generateUrl('tour_guide_entrance'));
                    } else {
                        $request->getSession()->getFlashBag()->set('failure', 'Wrong password.');
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', 'You are not leading any trips under that email.');
                }
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
        }

        return $this->render('BioTripBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Sign In'));
    }
    /**
     * @Route("/guide/trip/{id}", name="tour_guide_view_trip")
     * @Template()
     */
    public function guideViewTripAction(Request $request, Trip $trip = null) {
        if (!$request->getSession()->has('leaderEmail')) {

        } else if (!$trip) {
        
        } else if ($request->getSession()->get('leaderEmail') !== $trip->getEmail()) {

        } else {
            return array('trip' => $trip, 'title' => $trip->getTitle());
        }
    }

    public function findObjectByFieldValue($needle, $haystack, $field) {
        $getter = 'get'.ucFirst($field);

        foreach ($haystack as $straw) {
            if (call_user_func_array(array($straw, $getter), array()) === $needle) {
                return $straw;
            } 
        }
        return null;
    }

}