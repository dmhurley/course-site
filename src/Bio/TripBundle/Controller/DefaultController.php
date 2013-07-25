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
class DefaultController extends Controller
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
}
