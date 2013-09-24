<?php

namespace Bio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Symfony\Component\Form\FormError;

use Bio\UserBundle\Entity\User;
use Bio\UserBundle\Entity\AbstractUserStudent;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Validator\ExecutionContextInterface;

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
            $session->getFlashBag()->set('failure', "Incorrect username or password.");
        }

        return array('title' => "Log In", 'last_username' => $session->get(SecurityContext::LAST_USERNAME));
    }

    /**
     * @Route("/admin/register", name="register")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function registerAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('username', 'text', array(
                'label' => 'Username:',
                'constraints' => new Assert\NotBlank()
                )
            )
            ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => array('label' => 'Password:'),
                    'second_options' => array('label' => 'Repeat:')
                ))
            ->add('email', 'text', array(
                'label' => 'Email:',
                'constraints' => new Assert\Email()
                )
            )
            ->add('register', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $db = new Database($this, 'BioUserBundle:User');
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $pwd = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($pwd);
                $user->setRoles(array('ROLE_USER'));

                $db->add($user);
                $db->close();
                $flash->set('success', 'Registered account.');
                return $this->redirect($this->generateUrl('login'));
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        } else {
            // $flash->set('failure', 'An instructor will have to approve this account. Don\'t bother signing up if you are a student or don\'t have permission.');
        }

        return array('form' => $form->createView(), 'title' => 'Register Account');
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
                $db = new Database($this, 'BioUserBundle:AbstractUserStudent');
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