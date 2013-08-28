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
use Bio\StudentBundle\Entity\Student;


/** 
 * @Route("/admin/trip")
 */
class AdminController extends Controller
{
    /**
     * @Route("/manage", name="manage_trips")
     * @Template()
     */
    public function indexAction(Request $request)
    {	
    	$trip = new Trip();
    	$form = $this->get('form.factory')->createNamedBuilder('form', 'form', $trip)
    		->add('title', 'text', array('label' => 'Title:'))
    		->add('start', 'datetime', array('label' => 'Start:', 'attr' => array('class' => 'datetime')))
    		->add('end', 'datetime', array('label' => 'End:', 'attr' => array('class' => 'datetime')))
    		->add('max', 'integer', array('label' => 'Limit:'))
    		->add('email', 'email', array('label' => 'Leader Email:'))
            ->add('shortSum', 'textarea', array('label' => 'Short Summary:', 'attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
            ->add('longSum', 'textarea', array('label' => 'Long Summary:', 'attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('add', 'submit')
    		->getForm();
        $clone = clone $form;

        $db = new Database($this, 'BioTripBundle:TripGlobal');
        $global = $db->findOne(array());
        $globalForm = $this->get('form.factory')->createNamedBuilder('global', 'form', $global)
            ->add('opening', 'datetime', array('label' => 'Signup Start:', 'attr' => array('class' => 'datetime')))
            ->add('closing', 'datetime', array('label' => 'Evaluations Due:', 'attr' => array('class' => 'datetime')))
            ->add('maxTrips', 'integer', array('label' => "Max Trips:"))
            ->add('evalDue', 'integer', array('label' => "Days Until Late:"))
            ->add('guidePass', 'password', array('label' => 'Tour Guide Password:'))
            ->add('set', 'submit')
            ->getForm();

    	$db = new Database($this, 'BioTripBundle:Trip');

    	if ($request->getMethod() === "POST") {
            if ($request->request->has('form')){
        		$form->handleRequest($request);
        		if ($form->isValid()) {

        			$db->add($trip);
        		}
            }

            if ($request->request->has('global')) {
                $globalForm->handleRequest($request);

                if ($form->isValid()) {
                    $dbGlobal = $db->findOne(array());
                    $dbGlobal->setOpening($global->getOpening())
                        ->setClosing($global->getClosing())
                        ->setTourClosing($global->getTourClosing())
                        ->setMaxTrips($global->getMaxTrips());
                }
            }

            try {
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Saved change.');
                $form = $clone;
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Unable to save change.');
            }
    	}
    	$trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);
        return array('form' => $form->createView(), 'globalForm' => $globalForm->createView(), 'trips' => $trips, 'title' => "Manage Trips");
    }

    /**
     * @Route("/edit/{id}", name="edit_trip")
     * @Template()
     */
    public function editAction(Request $request, Trip $entity = null) {
    	
        if ($entity) {
        	$form = $this->createFormBuilder($entity)
        		->add('title', 'text', array('label' => 'Title:'))
        		->add('start', 'datetime', array('label' => 'Start:', 'attr' => array('class' => 'datetime')))
        		->add('end', 'datetime', array('label' => 'End:', 'attr' => array('class' => 'datetime')))
        		->add('max', 'integer', array('label' => 'Limit:'))
        		->add('email', 'email', array('label' => 'Leader Email:'))
                ->add('shortSum', 'textarea', array('label' => 'Short Summary:', 'attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
                 ->add('longSum', 'textarea', array('label' => 'Long Summary:', 'attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
        		->add('id', 'hidden')
        		->add('edit', 'submit')
        		->getForm();

        	if ($request->getMethod() === "POST") {
        		$form->handleRequest($request);

        		if ($form->isValid()) {
                    $db = new Database($this, 'BioTripBundle:Trip');
        			$db->close();

                    $request->getSession()->getFlashBag()->set('success', 'Evaluations edited.');
        			return $this->redirect($this->generateUrl('manage_trips'));
                } else {
                    $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                }
        	}

        	return array('form' => $form->createView(), 'trip' => $entity, 'title' => 'Edit Trip');
        }
    }

    /**
     * @Route("/delete/{id}", name="delete_trip")
     */
    public function deleteAction(Request $request, Trip $entity = null) {

        if ($entity) {
            $db = new Database($this, 'BioTripBundle:Trip');
            $db->delete($entity);
            try {
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Trip deleted.');
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Could not delete trip.');
            }
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find trip.');
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
    public function removeStudentAction(Request $request, Trip $trip = null, Student $student = null) {
        if ($student && $trip) {
            $db = new Database($this, 'BioTripBundle:Trip');
            $trip->removeStudent($student);
            $db->close();
            $request->getSession()->getFlashBag()->set('success', 'Removed student.');
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find that trip or student.');
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
                        $request->getSession()->getFlashBag()->set('failure', 'Error.');
                        return $this->redirect($this->generateUrl('trip_evals'));
                    }
                }
                $data = $request->request->get($key);
                $question->setType(is_array($data)?"multiple":"response");
                if ($question->getType() === 'multiple'){
                    if (!filter_var($data[1], FILTER_VALIDATE_INT)){
                        $request->getSession()->getFlashBag()->set('failure', 'Not a number.');
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

            /** DELETE ORPHANS **
             *      O --"OK"   *
             *    *-|-*        *
             *     /\          *
            ********************/

            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery('
                    SELECT q FROM BioTripBundle:EvalQuestion q
                    WHERE NOT EXISTS(SELECT 1 FROM BioTripBundle:Response t WHERE t.evalQuestion = q.id)
                    AND NOT EXISTS(SELECT g FROM BioTripBundle:TripGlobal g WHERE q MEMBER OF g.evalQuestions)
                ');
            $toDelete = $query->getResult();
            $db->deleteMany($toDelete);

            try {
                $db->close();
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Could not save questions.');
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
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('e')->from('BioTripBundle:Evaluation', 'e')
            ->where('e.trip = (SELECT t FROM BioTripBundle:Trip t WHERE t.id = :id)')
            ->andwhere('e.score IS NULL')
            ->setParameter('id', $id)
            ->getQuery();

        try {
            $eval = $query->getResult()[0];
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->set('success', 'All Evaluations reviewed for trip: '. $title .'.');
            return $this->redirect($this->generateUrl('trip_evals'));
        }


        $form = $this->createFormBuilder()
            ->add('score', 'choice', array('choices' => array(0, 15, 25)))
            ->add('i', 'hidden', array('mapped' => false, 'data' => $eval->getId()))
            ->add('d', 'hidden', array('mapped' => false, 'data' => $eval->getTimestamp()->format('Y-m-d H:i:s')))
            ->add('grade', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db = new Database($this, 'BioTripBundle:Evaluation');
                $dbEval = $db->findOne(array('id' => $form->get('i')->getData(), 'timestamp' => new \Datetime($form->get('d')->getData())));

                $dbEval->setScore($form->get('score')->getData());
                $db->close();
                return $this->redirect($this->generateUrl('eval_review', array('id' => $id, 'title' => $title)));
            }
        }


        return array('eval' => $eval, 'form' => $form->createView(), 'title' => 'Review');
    }

    /**
     * @Route("/evals/download/{id}", name="trip_download")
     * @Template("BioFolderBundle:Download:download.html.twig")
     */
    public function downloadAction(Request $request, Trip $trip = null) {
        if ($trip) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$trip->getTitle().'Evals.txt');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            echo "trip\tstudentID\tquestion\tanswer\ttimestamp\tscore\n";
            foreach ($trip->getEvals() as $eval) {
                foreach($eval->getAnswers() as $answer) {
                    echo $trip->getTitle()."\t";
                    echo $eval->getStudent()->getSid()."\t";
                    echo $answer->getEvalQuestion()->getID()."\t";
                    echo $answer->getAnswer()."\t";
                    echo $eval->getTimestamp()->format('Y-m-d H:i:s')."\t";
                    echo $eval->getScore()."\n";
                }
            }
            return array('text' => '');
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find trip.');
            return $this->redirect($this->generateUrl('trip_evals'));
        }
    }
}
