<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\ClickerBundle\Form\ClickerType;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/clicker")
 */
class PublicController extends Controller {

    /**
     * @Route("/register", name="register_clicker")
     * @Template()
     */
    public function registerAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();
    	$form = $this->createForm(new ClickerType(), null)
    		->add('Register', 'submit');

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
            $clicker = new Clicker();
    		
    		if ($form->isValid()){
                $student = $this->get('security.context')->getToken()->getUser();
	    		
                $db = new Database($this, 'BioClickerBundle:ClickerGlobal');
                $global = $db->findOne(array());
                $db = new Database($this, 'BioInfoBundle:Info');
                $info = $db->findOne(array());

				$db = new Database($this, 'BioClickerBundle:Clicker');
                if ($dbClicker = $db->findOne(array('student' => $student))){
                    $flash->set('success', "Clicker ID changed to #".$form->get('cid')->getData());
                    $clicker = $dbClicker;
                } else {
                    $flash->set('success', "Clicker ID #".$form->get('cid')->getData()." registered.");
                    $db->add($clicker);
                    $clicker->setStudent($student);
                }
				$clicker->setCid($form->get('cid')->getData());

				try {
					$db->close();

                    if ($global->getStart() <= new \DateTime() && $global->getNotifications()) {
                        $message = \Swift_Message::newInstance()
                            ->setSubject(
                                'New Clicker Registration: '. $clicker->getCid().
                                ' - '.$student->getFName()." ".$student->getLName()
                                )
                            ->setFrom($this->container->getParameter('mailer_dev_address'))
                            ->setTo($info->getEmail())
                            ->setBody(
                                    $student->getFName().' '. $student->getLName() .
                                    ' registered clicker #'. $clicker->getCid() .
                                    ' at '.(new \DateTime())->format('Y-m-d H:i:s').'.'
                                    );
                        $this->container->get('mailer')->send($message);
                    }

                    return $this->redirect($this->generateUrl('register_clicker'));
				} catch (BioException $e) {
					$flash->set('failure', "Invalid form.");
                    $form->get('cid')->addError(new FormError('Someone else is already registered to that clicker.'));
					$flash->get('success'); // remove the successful flash message that was set earlier
				}

	    	} else {
	    		$flash->set('failure', 'Invalid form.');
	    	}
    	}

        return array('form' => $form->createView(), 'title' => "Register Clicker");
    }
}
