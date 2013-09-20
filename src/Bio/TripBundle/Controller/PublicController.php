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
    public function testAction(Request $request) {
    	$session = $request->getSession();
    	$flash = $session->getFlashBag();

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());

        if ($global->getOpening() > new \DateTime()) {
            $flash->set('failure', 'Field trip signups start '.$global->getOpening()->format('F j, Y \a\t g:i a').'.');
        } else if ($global->getClosing() < new \DateTime()) {
            $flash->set('failure', 'Field trip signups are closed.');
        }

        return $this->tripAction($request, $session->get('studentID'), $global);
    }

    private function tripAction(Request $request, $id, $global) {
        $student = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $futureQuery = $em->createQueryBuilder()
            ->select('t')
            ->from('BioTripBundle:Trip', 't')
            ->where(':student NOT MEMBER OF t.students')
            ->andWhere('t.start > :now')
            ->orderBy('t.start', 'ASC')
            ->setParameter('student', $student)
            ->setParameter('now', new \DateTime())
            ->getQuery();
        $futureTrips = $futureQuery->getResult();

        $pastQuery = $em->createQueryBuilder()
            ->select('t')
            ->from('BioTripBundle:Trip', 't')
            ->where(':student NOT MEMBER OF t.students')
            ->andWhere('t.start <= :now')
            ->orderBy('t.start', 'ASC')
            ->setParameter('student', $student)
            ->setParameter('now', new \DateTime())
            ->getQuery();
        $pastTrips = $pastQuery->getResult();

        $yourQuery = $em->createQueryBuilder()
            ->select('t')
            ->from('BioTripBundle:Trip', 't')
            ->where(':student MEMBER OF t.students')
            ->orderBy('t.start', 'ASC')
            ->setParameter('student', $student)
            ->getQuery();
        $yourTrips = $yourQuery->getResult();

    	return $this->render('BioTripBundle:Public:browse.html.twig', array(
            'future' => $futureTrips,
            'past' => $pastTrips,
            'your' => $yourTrips,
            'global' => $global,
            'title' => 'Sign Up'
            )
        );

    }

    /**
     * @Route("/join/{id}", name="join_trip")
     */
    public function joinAction(Request $request, Trip $trip = null) {
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());
    	if (!$trip) {
    		$flash->set('failure', 'Trip could not be found.');
    	} else if ($trip->getStart() < new \DateTime()) {
            $flash->set('failure', 'Too late to join.');
        } else if ($global->getOpening() > new \DateTime() || $global->getClosing() < new \DateTime()) {
            // flash filled by testAction
        } else {
    		$student = $this->get('security.context')->getToken()->getUser();
            // TODO make sure trip hasn't passed!!!!!!
			if (count($trip->getStudents()) >= $trip->getMax()) {
				$flash->set('failure', 'Trip is full.');
			} else {
    			$trip->addStudent($student);
    			try {
    				$db->close();
    				$flash->set('success', 'Joined trip.');
    			} catch (BioException $e) {
    				$flash->set('failure', 'You cannot sign up for any more trips.');
    			}
    		}
    	}
    	return $this->redirect($this->generateUrl('trip_entrance'));
    }

    /**
     * @Route("/leave/{id}", name="leave_trip")
     */
    public function leaveAction(Request $request, Trip $trip = null) {
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());
    	if (!$trip) {
    		$flash->set('failure', 'Trip not found.');
    	} else if ($trip->getStart() < new \DateTime()) {
            $flash->set('failure', 'Too late to leave.');
        } else if ($global->getOpening() > new \DateTime() || $global->getClosing() < new \DateTime()) {
            // flash filled by test action
        }  else {
    		$student = $this->get('security.context')->getToken()->getUser();
            // TODO make sure trip hasn't passed!!!!!!
    		$trip->removeStudent($student);
            try {
              $db = new Database($this, 'BioTripBundle:Trip');  
    		  $db->close();
    		  $flash->set('success', 'Left trip.');
            } catch (BioException $e) {
                $flash->set('failure', 'Error.');
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
        $flash = $request->getSession()->getFlashBag();

        /****** GET STUFF FROM DATABASE ******/
        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array()); 
    
        $student = $this->get('security.context')->getToken()->getUser();

        $db = new Database($this, 'BioTripBundle:Evaluation');
        $eval = $db->findOne(array('trip' => $trip, 'student' => $student));

        /****** DOES STUDENT/TRIP EXISTS ******/
        if (!$trip) {
            $flash->set('failure', 'Could not find trip.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        /****** HAVE THEY NOT EVALUATED IT ******/
        if ($eval) {
            $flash->set('failure', 'You have already submitted an evaluation.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        /****** DID THEY GO ON TRIP ******/
        if (!in_array($student, $trip->getStudents()->toArray())) {
            $flash->set('failure', 'You did not attend this trip.');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        if ($trip->getEnd() > new \DateTime()) {
            $flash->set('failure', 'This trip has not occured');
            return $this->redirect($this->generateUrl('trip_entrance'));
        }

        /****** IS TOO LATE TO EVALUATE? ******/
        if ($global->getClosing() < new \DateTime()) {
            $flash->set('failure', 'It is too late to submit evaluations.');
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
                $flash->set('failure', 'Error.');
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
                        $flash->set('failure', 'Invalid IDs.');
                    }

                    $errors = $validator->validate($response);
                    if (count($errors) > 0){
                        $flash->set('failure', 'Invalid form.');
                        $areErrors = true;
                        $question->errors = $errors;
                    }
                }
                if (!$areErrors) {
                    try {
                        $db->close();
                        $flash->set('success', 'Evaluation saved.');
                        return $this->redirect($this->generateUrl('trip_entrance'));
                    } catch (BioException $e) {
                        $flash->set('failure', 'Could not save evaluation.');
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
        $flash = $request->getSession()->getFlashBag();

        $form = $this->createFormBuilder()
            ->add('email', 'text',          array('label' => 'Email:'))
            ->add('password', 'password',   array('label' => 'Password:'))
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
                        $flash->set('failure', 'Wrong password.');
                    }
                } else {
                    $flash->set('failure', 'You are not leading any trips under that email.');
                }
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }

        return $this->render('BioTripBundle:Public:sign.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Sign In'
            )
        );
    }
    /**
     * @Route("/guide/trip/{id}", name="tour_guide_view_trip")
     * @Template()
     */
    public function guideViewTripAction(Request $request, Trip $trip = null) {
        $flash = $request->getSession()->getFlashBag();

        if (!$request->getSession()->has('leaderEmail')) {
            $flash->set('failure', 'Not signed in.');
            return $this->redirect($this->generateUrl('tour_guide_entrance'));
        } else if (!$trip) {
            $flash->set('failure', 'Trip not found');
            return $this->redirect($this->generateUrl('tour_guide_entrance'));
        } else if ($request->getSession()->get('leaderEmail') !== $trip->getEmail()) {
            $flash->set('failure', 'Trip not found');
            return $this->redirect($this->generateUrl('tour_guide_entrance'));
        } else {
            return array('trip' => $trip, 'title' => $trip->getTitle());
        }
    }

    /**
     * @Route("/promo", name="promo")
     * @Template()
     */
    public function promoAction(Request $request) {
        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createFormBuilder($global)
                ->add('promo', 'textarea', array(
                    'attr' => array(
                        'class'=> 'tinymce',
                        'data-theme' => 'bio'
                        )
                    )
                )
                ->add('save', 'submit')
                ->getForm();

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);
                if($form->isValid()) {
                    $db->close();
                }
            }

            return array('form' => $form->createView(), 'title' => 'Field Trips');
        } else {
            return array('promo' => $global->getPromo(), 'title' => 'Field Trips');
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