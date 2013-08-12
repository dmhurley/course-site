<?php

namespace Bio\SwitchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\SwitchBundle\Entity\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/switch/requests", name="switch_requests")
     * @Template()
     */
    public function requestsAction(\Symfony\Component\HttpFoundation\Request $request)
    {	
    	$db = new Database($this, 'BioSwitchBundle:Request');
    	$requests = $db->find(array(), array(), false);

        return array('requests' => $requests, 'title' => 'Requests');
    }

    /**
     * @Route("/switch", name="request_switch")
     * @Template()
     */
    public function requestAction(\Symfony\Component\HttpFoundation\Request $request) {
    	$form = $this->createFormBuilder()
    		->add('sid', 'text', array('label' => 'Student ID:'))
    		->add('lName', 'text', array('label' => 'Last Name:'))
    		->add('sections', 'entity', array('class' => 'BioInfoBundle:Section', 'property' => 'descriptor', 'label' => 'Sections Wanted:', 'multiple'=>true))
    		->add('submit', 'submit', array('label' => 'Send Request'))
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$db = new Database($this, 'BioStudentBundle:Student');
    			$student = $db->findOne(array('sid' => $form->get('sid')->getData(), 'lName' => $form->get('lName')->getData()));

    			if (!$student) {
    				$request->getSession()->getFlashBag()->set('failure', 'Could not find student with that student ID and last name.');
    				return $this->redirect($this->generateUrl("request_switch"));
    			}

    			$db = new Database($this, 'BioInfoBundle:Section');
    			$section = $db->findOne(array('name' => $student->getSection()));

    			$r = new Request();
    			$r->setStatus(1)
    				->setStudent($student)
    				->setCurrent($section)
    				->setWants($form->get('sections')->getData());

    			$db->add($r);

    			/** FIND MATCH? **/
    			$em = $this->getDoctrine()->getManager();

    			// create array of ids to use IN
    			// if I used $r->getWant() it'd be comparing id's to objects
    			$ids = array();
    			foreach($r->getWant() as $section) {
    				$ids[] = $section->getId();
    			}

    			$query = $em->createQuery('
    					SELECT r
    					FROM BioSwitchBundle:Request r
    					WHERE r.current IN (:want)
    					AND :current MEMBER OF r.want
    					AND r.match IS NULL
    				')
    				->setParameter('want', $ids)
    				->setParameter('current', $r->getCurrent())
    				->setMaxResults(1);

    			try {
    				$match = $query->getSingleResult();
    				$match->setMatch($r);
    				$r->setMatch($match);
    			} catch (\Doctrine\Orm\NoResultException $e) {}
    			/** ********** **/

    			$db->close();
    		}
    	}
    	return array('form' => $form->createView(), 'title' => "Switch Sections");
    }
}
