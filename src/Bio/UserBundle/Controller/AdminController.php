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
    	$db = new Database($this, "BioUserBundle:User");

    	$users = $db->find(array(), array(), false);
    	
        return array('users' => $users, 'title' => 'Registered Users');
    }

    /**
     * @Route("/{type}mote/{id}", name="mote_user", requirements={"type" = "de|pro"})
     */
    public function moteUserAction(Request $request, $type, User $entity = null) {
        $flash = $request->getSession()->getFlashBag();

    	if ($entity && $entity->getRoles()[0] !== 'ROLE_SETUP' && $entity !== $this->getUser()) {
            $role = $entity->getRoles()[0];

            if ($type==="de") {
            	if ($role === "ROLE_ADMIN") {
            		$entity->setRoles(array("ROLE_USER"));
            	} else if ($role === "ROLE_SUPER_ADMIN") {
            		$entity->setRoles(array("ROLE_ADMIN"));
            	}
            } else {
            	if ($role === "ROLE_USER") {
            		$entity->setRoles(array("ROLE_ADMIN"));
            	} else if ($role === "ROLE_ADMIN") {
            		$entity->setRoles(array("ROLE_SUPER_ADMIN"));
            	}
            }
            $db = new Database($this, 'BioUserBundle:User');
            try {
                $db->close();
                $flash->set('success', ucfirst($type)."moted '".$entity->getUserName()."'.");
            } catch (BioException $e) {
                $flash->set('failure', 'Could not '.$type.'mote that user.');
            }
        } else {
            $flash->set('failure', 'Could not find that user.');
        }

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

        if ($entity && $entity->getRoles()[0] !== 'ROLE_SETUP' && $entity !== $this->getUser()) {
            $db = new Database($this, 'BioUserBundle:User');
            $db->delete($entity);
            $db->close();
            $flash->set('success', "Deleted '".$entity->getUsername()."'.");
        } else {
            $flash->set('failure', 'Could not find that user.');
        }

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
     * @Route("/reset/{id}", name="reset_password")
     */
    public function resetUserAction(Request $request, AbstractUserStudent $user = null) {
        $flash = $request->getSession()->getFlashBag();

        if (!$user) {
            $flash->set('failure', 'Could not find user.');
        } else if ($user->getEmail() === '') {
            $flash->set('failure', 'Cannot reset a password without an email.');
        } else {
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $pwd = substr(md5(rand()), 0, 7);
            $user->setPassword($encoder->encodePassword($pwd, $user->getSalt()));

            $db = new Database($this, 'BioInfoBundle:Info');
            $info = $db->findOne(array());

            $this->getDoctrine()->getManager()->flush();

            $message = \Swift_Message::newInstance()
                ->setSubject('Password Reset')
                ->setFrom($info->getEmail())
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody(
                    'Your new password for the biol'. $info->getCourseNumber() .
                    ' site is <code>'. $pwd .'</code>. Please sign in at '.
                    '<a href="'.$this->generateUrl('change_password').'">'.$this->generateUrl('change_password').'</a> '.
                    'with the username: <code>'.$user->getUsername().'</code> to change it.'
                    );

            $this->get('mailer')->send($message);
            $flash->set('success', 'Password reset.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('main_page'));
        }
    }
}
