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

    			if ($r->getStatus() === 1) {
    				return $this->setRequestAction($request, $r);
    			}

    			if ($r->getStatus() === 2) {

    			}
    		} else {
    			$session->invalidate();
    			$flash->set('failure', 'Not signed in.');
    		}
    	}

    	return $this->forward('BioTripBundle:Public:sign', array('request' => $request, 'redirect' => 'request_switch'));
    }

    private function setRequestAction($request, $r) {
    	$form = $this->createFormBuilder()
    		->add('want', 'entity', array('class' => 'BioInfoBundle:Section', 'property' => 'descriptor', 'multiple' => true, 'expanded' => true))
    		->add('request', 'submit')
    		->getForm();

    	return $this->render('BioSwitchBundle:Default:choose.html.twig', array('form' => $form->createView(), 'request' => $r));
    }
}
