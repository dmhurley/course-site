<?php

namespace Bio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;


/**
 * @Route("/user")
 */
class PublicController extends Controller
{
	/**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request) {
    	$session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        if ($error) {
            print_r([$error->getMessage(), $error->getFile(), $error->getLine()]);
            $session->getFlashBag()->set('failure', "Incorrect username or password.");
        }

        return array('title' => "Log In", 'last_username' => $session->get(SecurityContext::LAST_USERNAME));
    }


    /**
     * @Route("/reset", name="user_reset")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function selfResetAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $form = $this->createFormBuilder()
            ->add('username', 'text',   array('label' => 'Username:'))
            ->add('email', 'email',     array('label' => 'Email:'))
            ->add('reset', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db = $this->get('bio.database')->createDatabase('BioUserBundle:AbstractUserStudent');
                $users = $db->find(array('email' => $form->get('email')->getData()), array(), false);
                foreach($users as $user) {
                    if ($user->getUsername() === $form->get('username')->getData()) {
                        
                        $this->forward('BioUserBundle:Admin:reset', array('id' => $user->getId()));
                        $flash->set('success', 'New password sent.');
                        return $this->redirect($this->generateUrl('main_page'));
                    }
                }
                $error = new FormError("Could not find user with that name and email");
                $form->get('username')->addError($error);
                $form->get('email')->addError($error);
                $flash->set('failure', 'Invalid form.');
            }
        }

        return array('form' => $form->createView(), 'title' => 'Reset Password');
    }

    /**
     * @Route("/change", name="change_password")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function passwordAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $user = $this->get('security.context')->getToken()->getUser();
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        $form = $this->createFormBuilder()
            ->add('password', 'password', array(
                'label' => 'Current:',
                'constraints' => new Assert\Callback(
                    array(
                        'methods' => array(
                            function($password, $interface) use ($user, $encoder) {
                                $pwdGiven = $encoder->encodePassword($password, $user->getSalt());
                                if (!StringUtils::equals($pwdGiven, $user->getPassword())) {
                                    $interface->addViolationAt('password', 'Wrong password');
                                }
                            }
                        )
                    )
                )
                )
            )
            ->add('new', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => array('label' => 'New:'),
                    'second_options' => array('label' => 'Again:')
                ))
            ->add('change', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $newPwd = $encoder->encodePassword($form->get('new')->getData(), $user->getSalt());
                $user->setPassword($newPwd);
                $this->getDoctrine()->getManager()->flush();
                $this->get('security.context')->setToken(null);
                $request->getSession()->invalidate();
                $flash->set('success', 'Password changed. Please log in again.');
                return $this->redirect($this->generateUrl('login'));
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }

        return array('form' => $form->createView(), 'title' => 'Change Password');
    }
}