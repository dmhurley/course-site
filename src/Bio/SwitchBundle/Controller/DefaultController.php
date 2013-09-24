<?php

namespace Bio\SwitchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\SwitchBundle\Entity\Request;

use Doctrine\ORM\EntityRepository;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/switch/", name="switcher_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Section Switcher');
    }

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

        // get student/user from session
		$student = $this->get('security.context')->getToken()->getUser();

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $flash->set('failure', 'Only students can switch sections.');
            return $this->redirect($this->generateUrl('switch_requests'));
        } else if ($student && $student->getEmail() === '') {
            $flash->set('failure', 'You need to have an email to switch sections.');
            return $this->redirect($this->generateUrl('main_page'));
        }

        // get section
        $db = new Database($this, 'BioInfoBundle:Section');
        $section = $student->getSection();

        // see if they have a request
		$db = new Database($this, 'BioSwitchBundle:Request');
		$r = $db->findOne(array('student' => $student));

		if ($request->query->has('cancel') && $r) {
			$db->delete($r);
			$db->close();
			$flash->set('success', 'Request cancelled.');
			return $this->redirect($this->generateUrl('main_page'));
		}

		if (!$r) {
			return $this->setRequestAction($request, $student, $section, $db);
		}

		if ($r->getStatus() === 2) { 
			return $this->viewRequestAction($request, $r, $db);
		}

		if ($r->getStatus() === 3 || ($r->getMatch()->getStatus() === 3 && $r->getStatus() === 4)) {
			return $this->confirmationAction($request, $r, $db);
		}
        // should never reach here.. just in case...
        $flash->set('failure', 'Oops. Something went wrong.');
        return $this->redirect($this->generateUrl('main_page'));
    }

    private function setRequestAction($request, $student, $section, $db) {
        $flash = $request->getSession()->getFlashBag();

    	$form = $this->createFormBuilder()
    		->add('want', 'entity', array(
                'class' => 'BioInfoBundle:Section',
                'property' => 'id',
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function(EntityRepository $repo) {
                    return $repo->createQueryBuilder('e')
                        ->orderBy('e.name', 'ASC');
                    }
                )
            )
    		->add('request', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {

                $wants = $form->get('want')->getData();
                if (count($wants) > 0){
                     $r = new Request();
                     $db->add($r);
                     $r->setStatus(2)
                        ->setLastUpdated(new \DateTime())
                         ->setStudent($student)
                         ->setCurrent($section)
                         ->setWants($wants);

                    try {
        			     $db->close();
        			     $flash->set('success', 'Request sent.');
                    } catch (BioException $e) {
                        $flash->set('failure', 'Could not send request.');
                    }
                }

    			return $this->redirect($this->generateUrl('request_switch'));
    		}
    	}

    	return $this->render('BioSwitchBundle:Default:choose.html.twig', array(
            'form' => $form->createView(),
            'student' => $student,
            'title' => "Request Sections"
            )
        );
    }

    private function viewRequestAction($request, $r, $db) {
        $flash = $request->getSession()->getFlashBag();

    	$em = $this->getDoctrine()->getManager();

    	$ids = array();
		foreach($r->getWant() as $section) {
			$ids[] = $section->getId();
		}
    	$queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('r')
    		->from('BioSwitchBundle:Request', 'r')
            // change .id to last updated, this will keep the last update person as first pick
            ->leftJoin('BioSwitchBundle:Request', 'o', 'WITH', 'r.current = o.current AND r.lastUpdated > o.lastUpdated') 
            ->where('o.id IS NULL')
    		->andWhere('r.current IN (:want)')
            ->andWhere(':current MEMBER OF r.want')
            ->andWhere('r.status = 2')
            ->orderBy('r.id', 'ASC')
            ->setParameter('want', $ids)
            ->setParameter('current', $r->getCurrent());

    	$form = $this->createFormBuilder()
    		->add('match', 'entity', array(
                'class' => 'BioSwitchBundle:Request',
                'property' => 'status',
                'query_builder' => $queryBuilder,
                'expanded' => true
                )
            )
    		->add('submit', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$match = $form->get('match')->getData();

                if ($match){
        			$match->setMatch($r)
                        ->setLastUpdated(new \DateTime())
        				->setStatus(3);
        			$r->setMatch($match)
                        ->setLastUpdated(new \DateTime())
        				->setStatus(4);

                    try {
        			    $db->close();
                        $flash->set('success', 'Request sent.');
                    } catch (BioException $e) {
                        $flash->set('failure', 'Could not send request.');
                    }

                    $db = new Database($this, 'BioInfoBundle:Info');
                    $info = $db->findOne(array());
                    $message = \Swift_Message::newInstance()
                        ->setSubject("Someone wants to switch sections")
                        ->setFrom($info->getEmail())
                        ->setTo($r->getMatch()->getStudent()->getEmail())
                        ->setBody(
                            $this->renderView('BioSwitchBundle:Default:notificationEmail.html.twig', array(
                                'student' => $r->getMatch()->getStudent(),
                                'info' => $info
                                )
                            )
                        )
                        ->setPriority('high')
                        ->setContentType('text/html');

                    $this->get('mailer')->send($message);
                }

    			return $this->redirect($this->generateUrl('request_switch'));
    		}
    	}

		return $this->render('BioSwitchBundle:Default:matches.html.twig', array(
            'form' => $form->createView(),
            'request' => $r,
            'title' => 'Choose Section'
            )
        );
    }

    private function confirmationAction($request, $r, $db) {
        $flash = $request->getSession()->getFlashBag();

    	// check if the other requester cancelled request
    	if ($r->getMatch() === null) {
    		$r->setStatus(2);
            try {
                $db->close();
                $flash->set('failure', 'Partner cancelled their request.');
                return $this->redirect($this->generateUrl('request_switch'));
            } catch (BioException $e) {
                $flash->set('failure', 'Error.');
                return $this->redirect($this->generateUrl('main_page'));
            }
    	}

    	if ($request->query->has('decline')) {
            $m = $r->getMatch();
    		$r->setStatus(2)
                ->getMatch()
                ->setMatch(null);
            $m->setMatch(null)
                ->setStatus(2);
            
            try {
    		    $db->close();
                $flash->set('success', 'Request declined.');
            } catch (BioException $e) {
                $flash->set('failure', 'Error declining request.');
            }

            $db = new Database($this, 'BioInfoBundle:Info');
            $info = $db->findOne(array());
            $message = \Swift_Message::newInstance()
                ->setSubject('Switch Cancelled')
                ->setFrom($info->getEmail())
                ->addBcc($r->getStudent()->getEmail())
                ->addBcc($m->getStudent()->getEmail())
                ->setBody(
                    $this->renderView('BioSwitchBundle:Default:declineEmail.html.twig', 
                        array(
                            'a' => $r,
                            'b' => $m,
                            'c' => $info
                            )
                        )
                    )
                ->setPriority('high')
                ->setContentType('text/html');

            $this->get('mailer')->send($message);


    		return $this->redirect($this->generateUrl('request_switch'));
    	}

    	if ($r->getStatus() === 4 && $r->getMatch()->getStatus() === 3) {
    		return $this->render('BioSwitchBundle:Default:wait.html.twig', array(
                'request' => $r,
                'title' => 'Waiting...'
                )
            );
    	}

    	if ($r->getStatus() === 3) {
    		$form = $this->createFormBuilder()
    			->add("confirm", 'submit')
    			->getForm();

	    	if ($request->getMethod() === 'POST') {
                try {
                    $m = $r->getMatch();
                    $m->getStudent();

                    // database stuff
    	    		$r->setStatus(4); // probably unnecessary
                    $db->delete($r->getMatch());
                    $db->delete($r);
                    $db->close();

                    // only if db is closed do you send message
                    $db = new Database($this, 'BioInfoBundle:Info');
                    $info = $db->findOne(array()); 
    	    		$message = \Swift_Message::newInstance()
    	    			->setSubject('Switch Sections')
    	    			->setFrom($info->getEmail())
    	    			->addTo($r->getStudent()->getEmail())
    	    			->addTo($m->getStudent()->getEmail())
    	    			->setBody(
                            $this->renderView('BioSwitchBundle:Default:confirmationEmail.html.twig', 
                                array(
                                    'a' => $r,
                                    'b' => $m,
                                    'c' => $info
                                    )
                                )
                            )
                        ->setPriority('high')
                        ->setContentType('text/html');

    	    		$this->get('mailer')->send($message);

                    // sign out
    	    		$flash->set('success', 'Contact information sent.');
                } catch (Exception $e) {
                    $flash->set('failure', 'Error confirming match.');
                }
	    		return $this->redirect($this->generateUrl('main_page'));
	    	}

    		return $this->render('BioSwitchBundle:Default:confirm.html.twig', array(
                'form'=> $form->createView(),
                'request' => $r,
                'title' => 'Confirm Pairing'
                )
            );
    	}
    }
}
