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
    	$session = $request->getSession();
    	$flash = $session->getFlashBag();

    	if ($request->query->has('logout')) {
    		$session->invalidate();
    	}

    	if ($session->has('studentID')) {
    		$db = new Database($this, 'BioStudentBundle:Student');
    		$student = $db->findOne(array('id' => $session->get('studentID')));

    		if ($student) {
    			$db = new Database($this, 'BioSwitchBundle:Request');
    			$r = $db->findOne(array('student' => $student));

    			if (!$r) {
    				$db = new Database($this, 'BioInfoBundle:Section');
    				$section = $db->findOne(array('name' => $student->getSection()));

    				$r = new Request();
    				$db->add($r);
    				$r->setStatus(1)
    					->setStudent($student)
    					->setCurrent($section);
    				$db->close();
    			}

    			if ($request->query->has('cancel')) {
    				$db->delete($r);
    				$db->close();
    				$session->invalidate();
    				$flash->set('success', 'Request cancelled.');
    				return $this->redirect($this->generateUrl('request_switch'));
    			}

    			if ($r->getStatus() === 1) {
    				return $this->setRequestAction($request, $r, $db);
    			}

    			if ($r->getStatus() === 2) { 
    				return $this->viewRequestAction($request, $r, $db);
    			}

    			if ($r->getStatus() === 3 || ($r->getMatch()->getStatus() === 3 && $r->getStatus() === 4)) {
    				return $this->confirmationAction($request, $r, $db);
    			}
    		} else {
    			$session->invalidate();
    			$flash->set('failure', 'Not signed in.');
    		}
    	}

    	return $this->forward('BioTripBundle:Public:sign', array('request' => $request, 'redirect' => 'request_switch'));
    }

    private function setRequestAction($request, $r, $db) {
    	$form = $this->createFormBuilder()
    		->add('want', 'entity', array('class' => 'BioInfoBundle:Section', 'property' => 'descriptor', 'multiple' => true, 'expanded' => true))
    		->add('request', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$r->setWants($form->get('want')->getData())
    				->setStatus(2);
    			$db->close();
    			$request->getSession()->getFlashBag()->set('success', 'Requests saved.');
    			return $this->redirect($this->generateUrl('request_switch'));
    		}
    	}
    	$db->close();

    	return $this->render('BioSwitchBundle:Default:choose.html.twig', array('form' => $form->createView(), 'request' => $r, 'title' => "Request Sections"));
    }

    private function viewRequestAction($request, $r, $db) {
    	$em = $this->getDoctrine()->getManager();

    	$ids = array();
		foreach($r->getWant() as $section) {
			$ids[] = $section->getId();
		}
    	$queryBuilder = $em->createQueryBuilder()
    		->select('r')
    		->from('BioSwitchBundle:Request', 'r')
    		->where('r.current IN (:want)')
    		->andWhere(':current MEMBER OF r.want')
    		->andWhere('r.status = 2')
    		->setParameter('want', $ids)
    		->setParameter('current', $r->getCurrent());

    	$form = $this->createFormBuilder()
    		->add('match', 'entity', array('class' => 'BioSwitchBundle:Request', 'property' => 'status', 'query_builder' => $queryBuilder, 'expanded' => true))
    		->add('submit', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$match = $form->get('match')->getData();
    			$match->setMatch($r)
    				->setStatus(3);
    			$r->setMatch($match)
    				->setStatus(4);
    			$db->close();

    			$request->getSession()->getFlashBag()->set('success', 'Request sent.');
    			return $this->redirect($this->generateUrl('request_switch'));
    		}
    	}

		return $this->render('BioSwitchBundle:Default:matches.html.twig', array('form' => $form->createView(), 'request' => $r, 'title' => 'Choose Section'));
    }

    private function confirmationAction($request, $r, $db) {
    	// check if the other requester cancelled request
    	if ($r->getMatch() === null) {
    		$r->setStatus(2);
    		$db->close();
    		$request->getSession()->getFlashBag()->set('failure', 'Partner cancelled their request.');
    		return $this->redirect($this->generateUrl('request_switch'));
    	}

    	if ($request->query->has('decline')) {
    		$r->getMatch()->setMatch(null);

    		$r->setMatch(null)
    			->setStatus(2);

    		$db->close();
    		$request->getSession()->getFlashBag()->set('success', 'Matching declined.');
    		return $this->redirect($this->generateUrl('request_switch'));
    	}

    	if ($r->getStatus() === 4 && $r->getMatch()->getStatus() === 3) {
    		return $this->render('BioSwitchBundle:Default:wait.html.twig', array('request' => $r, 'title' => 'Waiting...'));
    	}

    	if ($r->getStatus() === 3) {
    		$form = $this->createFormBuilder()
    			->add("confirm", 'submit')
    			->getForm();

	    	if ($request->getMethod() === 'POST') {
	    		$r->setStatus(4);
	    		$message = \Swift_Message::newInstance()
	    			->setSubject('Switch Sections')
	    			->setFrom('nickclaw@gmail.com')
	    			->addCc($r->getStudent()->getEmail())
	    			->addCc($r->getMatch()->getStudent()->getEmail())
	    			->setBody(
	    					'talky talky talk.'
	    				);

	    		$this->get('mailer')->send($message);
	    		
	    		$db->delete($r->getMatch());
	    		$db->delete($r);
	    		$request->getSession()->invalidate();
	    		$request->getSession()->getFlashBag()->set('success', 'Contact information sent.');

	    		$db->close();
	    		return $this->redirect($this->generateUrl('request_switch'));
	    	}

    		return $this->render('BioSwitchBundle:Default:confirm.html.twig', array('form'=> $form->createView(), 'request' => $r, 'title' => 'Confirm Pairing'));
    	}
    }
}