<?php

namespace Bio\TripBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\TripBundle\Entity\Trip;
use Bio\TripBundle\Entity\Evaluation;
use Bio\TripBundle\Entity\EvalQuestion;
use Bio\TripBundle\Entity\Response;
use Bio\UserBundle\Entity\AbstractUserStudent;


/** 
 * @Route("/admin/trip")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="trip_instruct")
     * @Template()
     */
    public function instructionAction(Request $request) {
        return array('title' => 'Field Trips');
    }

    /**
     * @Route("/manage", name="manage_trips")
     * @Template()
     */
    public function indexAction(Request $request)
    {	
        $flash = $request->getSession()->getFlashBag();

    	$trip = new Trip();
    	$form = $this->get('form.factory')->createNamedBuilder('form', 'form', $trip)
    		->add('title', 'text', array('label' => 'Title:'))
    		->add('start', 'datetime', array(
                'label' => 'Start:',
                'attr' => array('class' => 'datetime')
                )
            )
    		->add('end', 'datetime', array(
                'label' => 'End:',
                'attr' => array('class' => 'datetime')
                )
            )
    		->add('max', 'integer', array('label' => 'Limit:'))
    		->add('email', 'email', array('label' => 'Leader Email:'))
            ->add('shortSum', 'textarea', array(
                'label' => 'Short Summary:',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('longSum', 'textarea', array(
                'label' => 'Long Summary:',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
    		->add('add', 'submit')
    		->getForm();

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());
        $globalForm = $this->get('form.factory')->createNamedBuilder('global', 'form', $global)
            ->add('opening', 'datetime', array(
                'label' => 'Signup Start:',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('closing', 'datetime', array(
                'label' => 'Evaluations Due:',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('maxTrips', 'integer', array('label' => "Max Trips:"))
            ->add('evalDue', 'integer',  array('label' => "Days Until Late:"))
            ->add('instructions', 'textarea', array(
                'label' => 'Trip Instructions',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('guidePass', 'password', array(
                'label' => 'Tour Guide Password:',
                'always_empty' => false,
                'attr' => array('value' => $global->getGuidePass())
                )
            )
            ->add('set', 'submit')
            ->getForm();

    	$db = new Database($this, 'BioTripBundle:Trip');

    	if ($request->getMethod() === "POST") {
            $isValid = true;

            if ($request->request->has('form')){
        		$form->handleRequest($request);
        		if ($form->isValid()) {
        			$db->add($trip);
        		} else {
                    $isValid = false;
                }
            }

            if ($request->request->has('global')) {
                $globalForm->handleRequest($request);
                if (!$globalForm->isValid()) {
                    $isValid = false;
                }
            }
            if ($isValid) {
                try {
                    $db->close();
                    $flash->set('success', 'Saved change.');
                    return $this->redirect($this->generateUrl('manage_trips'));
                } catch (BioException $e) {
                    $flash->set('failure', 'Unable to save change.');
                }
            } else {
                $flash->set('failure', 'Invalid Form.');
            }
    	}
    	$trips = $db->find(
            array(),
            array('start' => 'ASC', 'end' => 'ASC'),
            false
            );

        return array(
            'form' => $form->createView(),
            'globalForm' => $globalForm->createView(),
            'trips' => $trips,
            'title' => "Manage Trips"
            );
    }

    /**
     * @Route("/edit/{id}", name="edit_trip")
     * @Template()
     */
    public function editAction(Request $request, Trip $entity = null) {
    	$flash = $request->getSession()->getFlashBag();

        if ($entity) {
        	$form = $this->createFormBuilder($entity)
        		->add('title', 'text', array('label' => 'Title:'))
        		->add('start', 'datetime', array(
                    'label' => 'Start:',
                    'attr' => array('class' => 'datetime')
                    )
                )
        		->add('end', 'datetime', array(
                    'label' => 'End:',
                    'attr' => array('class' => 'datetime')
                    )
                )
        		->add('max', 'integer', array('label' => 'Limit:'))
        		->add('email', 'email', array('label' => 'Leader Email:'))
                ->add('shortSum', 'textarea', array(
                    'label' => 'Short Summary:',
                    'attr' => array(
                        'class' => 'tinymce',
                        'data-theme' => 'bio'
                        )
                    )
                )
                 ->add('longSum', 'textarea', array(
                    'label' => 'Long Summary:',
                    'attr' => array(
                        'class' => 'tinymce',
                        'data-theme' => 'bio'
                        )
                    )
                 )
        		->add('id', 'hidden')
        		->add('save', 'submit')
        		->getForm();

        	if ($request->getMethod() === "POST") {
        		$form->handleRequest($request);

        		if ($form->isValid()) {
                    $db = new Database($this, 'BioTripBundle:Trip');
                    try {
        			    $db->close();
                        $flash->set('success', 'Trip edited.');
                        return $this->redirect($this->generateUrl('manage_trips'));
                    } catch (BioException $e) {
                        $flash->set('failure', 'Unable to save changes.');
                    }
                } else {
                    $flash->set('failure', 'Invalid form.');
                }
        	}

        	return array(
                'form' => $form->createView(),
                'trip' => $entity, 
                'title' => 'Edit Trip'
                );
        }
    }

    /**
     * @Route("/delete/{id}", name="delete_trip")
     */
    public function deleteAction(Request $request, Trip $entity = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($entity) {
            $db = new Database($this, 'BioTripBundle:Trip');
            $db->delete($entity);
            try {
                $db->close();
                $flash->set('success', 'Trip deleted.');
            } catch (BioException $e) {
                $flash->set('failure', 'Could not delete trip.');
            }
        } else {
            $flash->set('failure', 'Could not find trip.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('manage_trips'));
        }
    }

    // TODO error handling
    /**
     * @Route("/edit/{id}/remove/{sid}", name="remove_student")
     * @ParamConverter("trip", options={"mapping": {"id": "id"}})
     * @ParamConverter("student", options={"mapping": {"sid": "id"}})
     */
    public function removeStudentAction(Request $request, Trip $trip = null, AbstractUserStudent $student = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($student && $trip) {
            $db = new Database($this, 'BioTripBundle:Trip');
            $trip->removeStudent($student);
            $db->close();
            $flash->set('success', 'Removed student.');
        } else {
            $flash->set('failure', 'Could not find that trip or student.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('edit_trip', array('id' => $trip->getId())));
        }
    }

    /**
     * @Route("/evals", name="trip_evals")
     * @Template()
     */
    public function evalsAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());
        $db = new Database($this, 'BioTripBundle:EvalQuestion');

        if ($request->getMethod() === "POST") {
            $evalQuestions = array();
            foreach($request->request->keys() as $key) {
                if ($key < 0) {
                    $question = new EvalQuestion();
                    $db->add($question);
                } else {
                    $question = $db->findOne(array('id' => $key));

                    if ($question === null) {
                        $flash->set('failure', 'Error.');
                        return $this->redirect($this->generateUrl('trip_evals'));
                    }
                }
                $data = $request->request->get($key);
                $question->setType(is_array($data)?"multiple":"response");
                if ($question->getType() === 'multiple'){
                    if (!filter_var($data[1], FILTER_VALIDATE_INT)){
                        $flash->set('failure', 'Not a number.');
                        return $this->redirect($this->generateUrl('trip_evals'));
                    } else {
                        $data[0] = filter_var($data[0], FILTER_SANITIZE_STRING);
                    }

                    $question->setData($data);
                } else {
                    $data = filter_var($data, FILTER_SANITIZE_STRING);
                    $question->setData(array($data));
                }


                $evalQuestions[] = $question;
            }
            $global->setEvalQuestions($evalQuestions);

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery('
                    SELECT q 
                    FROM BioTripBundle:EvalQuestion q
                    WHERE NOT EXISTS(
                        SELECT 1 
                        FROM BioTripBundle:Response t
                        WHERE t.evalQuestion = q.id
                    )
                    AND NOT EXISTS(
                        SELECT g
                        FROM BioTripBundle:TripGlobal g
                        WHERE q MEMBER OF g.evalQuestions
                    )
                ');
            $toDelete = $query->getResult();
            $db->deleteMany($toDelete);

            try {
                $db->close();
                $flash->set('success', 'Evaluation questions saved.');
            } catch (BioException $e) {
                $flash->set('failure', 'Unable to save changes.');
            }
        }

        $questions = $global->getEvalQuestions();        
        $db = new Database($this, 'BioTripBundle:Trip');
        $trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);


        return array('trips' => $trips, 'questions' => $questions, 'title' => 'Evaluations');
    }

    /**
     * @Route("/evals/review/{id}/{title}", name="eval_review")
     * @Template()
     */
    public function reviewAction(Request $request, $id, $title) {
        $flash = $request->getSession()->getFlashBag();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('e')
            ->from('BioTripBundle:Evaluation', 'e')
            ->where('e.trip = (
                SELECT t
                FROM BioTripBundle:Trip t
                WHERE t.id = :id)
            ')
            ->andwhere('e.score IS NULL')
            ->setParameter('id', $id)
            ->getQuery();

        $evals = $query->getResult();
        if (count($evals) === 0) {
            $flash->set('success', 'All Evaluations reviewed for trip: '. $title .'.');
            return $this->redirect($this->generateUrl('trip_evals'));
        } else {
            $eval = $evals[0];
        }


        $form = $this->createFormBuilder()
            ->add('score', 'choice', array(
                'choices' => array(
                    0 => 0,
                    15 => 15,
                    20 => 20,
                    25 => 25
                    )
                )
            )
            ->add('i', 'hidden', array(
                'mapped' => false,
                'data' => $eval->getId()
                )
            )
            ->add('d', 'hidden', array(
                'mapped' => false,
                'data' => $eval->getTimestamp()->format('Y-m-d H:i:s')
                )
            )
            ->add('grade', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db = new Database($this, 'BioTripBundle:Evaluation');
                $dbEval = $db->findOne(
                    array(
                        'id' => $form->get('i')->getData(),
                        'timestamp' => new \Datetime($form->get('d')->getData())
                        )
                    );

                $dbEval->setScore($form->get('score')->getData());
                $db->close();
                return $this->redirect($this->generateUrl('eval_review', array('id' => $id, 'title' => $title)));
            }
        }


        return array('eval' => $eval, 'form' => $form->createView(), 'title' => 'Review');
    }

    /**
     * @Route("/evals/download/{id}", name="trip_download")
     */
    public function downloadAction(Request $request, Trip $trip = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($trip) {
            $responseText = ["trip\tstudentID\tquestion\tanswer\ttimestamp\tscore"];
            foreach ($trip->getEvals() as $eval) {
                foreach($eval->getAnswers() as $answer) {
                    $responseText[] = $trip->getTitle()."\t".
                        $eval->getStudent()->getSid()."\t".
                        $answer->getEvalQuestion()->getID()."\t".
                        $answer->getAnswer()."\t".
                        $eval->getTimestamp()->format('Y-m-d H:i:s')."\t".
                        $eval->getScore();
                }
            }

             $response = $this->render('BioExamBundle:Admin:download.html.twig', array(
                'text' => implode("\n", $responseText)
                )
            );
            $response->headers->set(
                "Content-Type", 'application/plaintext'
                );

            $response->headers->set(
                'Content-Disposition', ('attachment; filename="'.$trip->getTitle().'_eval.txt"')
                );
            return $response;

        } else {
            $flash->set('failure', 'Could not find trip.');
            return $this->redirect($this->generateUrl('trip_evals'));
        }
    }
}
