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
    	$trips = $db->find(array(), array(), false);
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
}
