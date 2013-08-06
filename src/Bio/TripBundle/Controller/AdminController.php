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
use Bio\TripBundle\Entity\Query;
use Bio\TripBundle\Entity\Response;

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
    	$form = $this->createFormBuilder($trip)
    		->add('title', 'text', array('label' => 'Title:'))
    		->add('shortSum', 'textarea', array('label' => 'Short Summary:'))
    		->add('longSum', 'textarea', array('label' => 'Long Summary:'))
    		->add('start', 'datetime', array('label' => 'Start:', 'attr' => array('class' => 'datetime')))
    		->add('end', 'datetime', array('label' => 'End:', 'attr' => array('class' => 'datetime')))
    		->add('max', 'integer', array('label' => 'Limit:'))
    		->add('email', 'email', array('label' => 'Leader Email:'))
    		->add('add', 'submit')
    		->getForm();

    	$db = new Database($this, 'BioTripBundle:Trip');

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {

    			$db->add($trip);
    			$db->close();
    		}
    	}
    	$trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);
        return array('form' => $form->createView(), 'trips' => $trips, 'title' => "Manage Trips");
    }

    /**
     * @Route("/edit", name="edit_trip")
     * @Template()
     */
    public function editAction(Request $request) {
    	$db = new Database($this, 'BioTripBundle:Trip');

    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
    		$id = $request->query->get('id');
    		$entity = $db->findOne(array('id' => $id));
    	} else {
    		$entity = new Trip();
    	}

    	$form = $this->createFormBuilder($entity)
    		->add('title', 'text', array('label' => 'Title:'))
    		->add('shortSum', 'textarea', array('label' => 'Short Summary:'))
    		->add('longSum', 'textarea', array('label' => 'Long Summary:'))
    		->add('start', 'datetime', array('label' => 'Start:', 'attr' => array('class' => 'datetime')))
    		->add('end', 'datetime', array('label' => 'End:', 'attr' => array('class' => 'datetime')))
    		->add('max', 'integer', array('label' => 'Limit:'))
    		->add('email', 'email', array('label' => 'Leader Email:'))
    		->add('id', 'hidden')
    		->add('edit', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$dbEntity = $db->findOne(array('id' => $entity->getId()));
    			$dbEntity->setTitle($entity->getTitle())
    				->setShortSum($entity->getShortSum())
    				->setLongSum($entity->getLongSum())
    				->setStart($entity->getStart())
    				->setEnd($entity->getEnd())
    				->setMax($entity->getMax())
    				->setEmail($entity->getEmail());

    			$db->close();

    			return $this->redirect($this->generateUrl('manage_trips'));
    		}
    	}

    	return array('form' => $form->createView(), 'students' => $entity->getStudents(), 'title' => 'Edit Trip');
    }

    /**
     * @Route("/delete", name="delete_trip")
     */
    public function deleteAction(Request $request) {
        if ($request->query->get('id')){
            $db = new Database($this, 'BioTripBundle:Trip');
            $trip = $db->findOne(array('id' => $request->query->get('id')));

            if (!$trip) {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that trip.');
            } else {
                $db->delete($trip);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Trip deleted.');
            }
        } else {
            $request->getSession()->getFlashBag()->set('success', 'Question deleted.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('manage_trips'));
        }
    }

    // TODO error handling
    /**
     * @Route("/edit/remove", name="remove_student")
     */
    public function removeStudentAction(Request $request) {
        if ($request->query->has('id') && $request->query->has('tid')) {
            $studentID = $request->query->get('id');
            $tripID = $request->query->get('tid');

            $db = new Database($this, 'BioStudentBundle:Student');
            $student = $db->findOne(array('id' => $studentID));

            $db = new Database($this, 'BioTripBundle:Trip');
            $trip = $db->findOne(array('id' => $tripID));

            if (!$trip || !$student) {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that trip or student.');
            } else {
                $trip->removeStudent($student);
                $db->close();
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('edit_trip').'?id='.$tripID);
        }
    }

    /**
     * @Route("/evals", name="trip_evals")
     * @Template()
     */
    public function evalsAction(Request $request) {
        $db = new Database($this, 'BioTripBundle:Query');

        if ($request->getMethod() === "POST") {
            $evalQuestions = array();
            foreach($request->request->keys() as $key) {
                if ($key < 0) {
                    $query = new Query();
                    $db->add($query);
                } else {
                    $query = $db->findOne(array('id' => $key));
                }
                $evalQuestions[] = $query;
                $query->setQuestion($request->request->get($key));
            }
        }
        // save $evalQuestions to TripGlobal
        $db->close();

        $queries = $db->find(array(), array(), false);

        $db = new Database($this, 'BioTripBundle:Trip');
        $trips = $db->find(array(), array('start' => 'ASC', 'end' => 'ASC'), false);

        return array('trips' => $trips, 'queries' => $queries, 'title' => 'Evaluations');
    }

    /**
     * @Route("/download/{id}", name="trip_download")
     * @Template("BioFolderBundle:Download:download.html.twig")
     */
    public function downloadAction(Request $request, $id) {
        $db = new Database($this, 'BioTripBundle:Trip');
        $trip = $db->findOne(array('id' => $id));

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$trip->getTitle().'Evals.txt');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        echo "sid\teval\n";
        foreach ($trip->getEvals() as $eval) {
            echo $eval->getStudent()->getSid()."\t";
            echo $eval->getEval()."\n";
        }
        return array('text' => '');
    }
}
