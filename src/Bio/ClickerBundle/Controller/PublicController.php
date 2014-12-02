<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
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

    		if ($form->isValid()){
                $student = $this->get('security.context')->getToken()->getUser();

                // try to register clicker
                $em = $this->getDoctrine()->getManager();
                $result = $em->getRepository('BioClickerBundle:Clicker')
                    ->registerClicker($student, $form->get('cid')->getData());


                if ($result['success']) {
                    $flash->set('success', $result['message']);
                    $clicker = $result['clicker'];

                    // send email if settings allow
                    $db = new Database($this, 'BioClickerBundle:ClickerGlobal');
                    $global = $db->findOne(array());

                    if ($global->getStart() <= new \DateTime() &&
                        $global->getNotifications() &&
                        $global->getNotificationEmail()) {
                        $message = \Swift_Message::newInstance()
                            ->setSubject(
                                'New Clicker Registration: '. $clicker->getCid().
                                ' - '.$student->getFName()." ".$student->getLName()
                                )
                            ->setFrom($this->container->getParameter('mailer_dev_address'))
                            ->setTo($global->getNotificationEmail())
                            ->setBody(
                                    $student->getFName().' '. $student->getLName() .
                                    ' registered clicker #'. $clicker->getCid() .
                                    ' at '.(new \DateTime())->format('Y-m-d H:i:s').'.'
                                    );
                        $this->container->get('mailer')->send($message);
                    }

                    return $this->redirect($this->generateUrl('register_clicker'));
                } else {
                    $flash->set('failure', "Invalid form.");
                    $form->get('cid')->addError(new FormError($result['message']));
                }

	    	} else {
	    		$flash->set('failure', 'Invalid form.');
	    	}
    	}

        return array('form' => $form->createView(), 'title' => "Register Clicker");
    }
}
