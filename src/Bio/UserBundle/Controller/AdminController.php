<?php

namespace Bio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Bio\UserBundle\Entity\User;
use Bio\UserBundle\Entity\AbstractUserStudent;
use Symfony\Component\Validator\Constraints as Assert;
use Bio\DataBundle\Exception\BioException;


/**
 * @Route("/admin/user")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="view_users")
     * @Template()
     */
    public function indexAction(Request $request)
    {
    	$users = $this->getDoctrine()
                      ->getManager()
                      ->getRepository('BioUserBundle:User')
                      ->findAll();

        return array('users' => $users, 'title' => 'Registered Users');
    }

    /**
     * @Route("/{type}mote/{id}", name="mote_user", requirements={"type" = "de|pro"})
     */
    public function moteUserAction(Request $request, $type, User $user = null) {
        $flash = $request->getSession()->getFlashBag();

        // try to change user role
        $result = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('BioUserBundle:User')
                     ->mote($user, $type === 'pro');

        $flash->set(
            $result['success'] ? 'success' : 'failure',
            $result['message']
        );

        // if redirect back to last page if possible
        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_users'));
        }
    }

    /**
     * @Route("/delete/{id}", name="delete_user")
     */
    public function deleteUserAction(Request $request, User $entity = null) {
        $flash = $request->getSession()->getFlashBag();

        $result = $this->getDoctrine()
                       ->getManager()
                       ->getRepository('BioUserBundle:User')
                       ->delete($entity);

        $flash->set(
            $result['success'] ? 'success' : 'failure',
            $result['message']
        );

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_users'));
        }

    }

    /**
     * @Route("/register", name="register")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function registerUserAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $user = new User();

        // TODO move form out to a FormType
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
                )
            )
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
                $em = $this->getDoctrine()->getManager();

                // set some default user stuff
                $user->setRoles(array('ROLE_USER'));

                // add user to db
                $em->persist($user);

                // set password and save
                $result = $em->getRepository('BioUserBundle:AbstractUserStudent')
                    ->changePassword(
                        $user,
                        $this->get('security.encoder_factory'),
                        $user->getPassword()
                    );

                $flash->set(
                    $result['success'] ? 'success' : 'failure',
                    $result['success'] ? 'User created.' : 'Could not create user.'
                );
                return $this->redirect($this->generateUrl('login'));
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }

        return array('form' => $form->createView(), 'title' => 'Register Account');
    }

    /**
     * @Route("/reset/{id}", name="reset_password")
     */
    public function resetUserAction(Request $request, AbstractUserStudent $user = null) {
        $flash = $request->getSession()->getFlashBag();

        // cannnot reset password with an email
        if ($user && !$user->getEmail()) {
            $flash->set('failure', 'Cannot reset a password with an email.');
        } else {

            // try to reset password
            $result=$this->getDoctrine()
                         ->getManager()
                         ->getRepository('BioUserBundle:AbstractUserStudent')
                         ->reset($user, $this->get('security.encoder_factory'));

            // send email if it worked
            if ($result['success']) {

                $db = new Database($this, 'BioInfoBundle:Info');
                $info = $db->findOne(array());


                $message = \Swift_Message::newInstance()
                    ->setSubject('Password Reset')
                    ->setFrom($info->getEmail())
                    ->setTo($user->getEmail())
                    ->setContentType('text/html')
                    ->setBody(
                        'Your new password for the biol'. $info->getCourseNumber() .
                        ' site is <code>'. $result['password'] .'</code>. Please sign in at '.
                        '<a href="'.$this->generateUrl('change_password', array(), true).'">'.
                        $this->generateUrl('change_password', array(), true).'</a> '.
                        'with the username: <code>'.$user->getUsername().'</code> to change it.'
                    );
                $this->get('mailer')->send($message);
            }

            $flash->set(
                $result['success'] ? 'success' : 'failure',
                $result['message']
            );
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('main_page'));
        }
    }
}
