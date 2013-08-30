<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Validator\ExecutionContextInterface;

use Bio\DataBundle\Objects\Database;
use Bio\UserBundle\Entity\User;
use Bio\InfoBundle\Entity\Announcement;
use Symfony\Component\Validator\Constraints as Assert;


class DefaultController extends Controller
{
    /**
     * @Route("/admin/", name="main_admin_page")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Admin Page');
    }

    /**
     * @Route("/", name="main_page")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $db = new Database($this, 'BioInfoBundle:Person');
        $instructors = $db->find(array('title' => 'instructor'), array(), false);
        $tas = $db->find(array('title' => 'ta'), array(), false);
        $coordinators = $db->find(array('title' => 'coordinator'), array(), false);

        $db = new Database($this, 'BioInfoBundle:Info');
        $info = $db->findOne(array());

        $db = new Database($this, 'BioInfoBundle:Section');
        $sections = $db->find(array(), array(), false);

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT a FROM BioInfoBundle:Announcement a WHERE a.expiration > :now AND a.timestamp < :now ORDER BY a.expiration ASC'
        )->setParameter('now', new \DateTime());
        $anns = $query->getResult();

        $db = new Database($this, 'BioInfoBundle:Link');
        $links = $db->find(array('location' => 'content'), array(), false);

        return array('instructors' => $instructors, 'tas' => $tas, 'coordinators' => $coordinators, 'info' => $info,
            'sections' => $sections, 'anns' => $anns, 'links' => $links, 'title' => "Welcome");
    }

    /**
     * @Route("/user/login", name="login")
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
            $request->getSession()->getFlashBag()->set('failure', "Incorrect username or password.");
        }

        return array('title' => "Log In", 'last_username' => $session->get(SecurityContext::LAST_USERNAME));
    }

    /**
     * @Route("/user/register", name="register")
     * @Template()
     */
    public function registerAction(Request $request) {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('username', 'text', array('label' => 'Username:', 'constraints' => new Assert\NotBlank()))
            ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => array('label' => 'Password:'),
                    'second_options' => array('label' => 'Repeat:')
                ))
            ->add('email', 'text', array('label' => 'Email:', 'constraints' => new Assert\Email()))
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
                    $request->getSession()->getFlashBag()->set('success', 'Registered account.');
                    return $this->redirect($this->generateUrl('login'));
                } else {
                    $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                }
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'An instructor will have to approve this account. Don\'t bother signing up without permission');
            }

            return array('form' => $form->createView(), 'title' => 'Register Account');
    }

    /**
     * @Route("/folder/{id}", name="public_folder")
     * @Template()
     */
    public function folderAction(Request $request, $id) {
        $db = new Database($this, 'BioFolderBundle:Folder');
        $root = $db->findOne(array('id' => $id));

        if (!$root || $root->getPrivate()) {
            $request->getSession()->getFlashBag()->set('failure', 'Folder does not exist.');
            if ($request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            } else {
                return $this->redirect($this->generateUrl('public_folder', array('id' => 1)));
            }
        }

        return array('root' => $root, 'title' => $root->getName().' Folder');
    }

    /**
     * @Route("/links", name="public_links")
     * @Template()
     */
    public function linkAction(Request $request) {
        $db = new Database($this, 'BioInfoBundle:Link');
        $sidebar = $db->find(array('location' => 'sidebar'), array(), false);
        $mainpage = $db->find(array('location' => 'content'), array(), false);

        return array('sidelinks' => $sidebar, 'mainlinks' => $mainpage, 'title' => 'Links');
    }

    /**
     * @Route("/user/change", name="change_password")
     * @Template()
     */
    public function passwordAction(Request $request) {
        $user = $this->get('security.context')->getToken()->getUser();
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        $form = $this->createFormBuilder()
            ->add('password', 'password', array('label' => 'Current:', 'constraints' => new Assert\Callback(array('methods' => array(function($password, $interface) use ($user, $encoder) {
                $pwdGiven = $encoder->encodePassword($password, $user->getSalt());
                if (!StringUtils::equals($pwdGiven, $user->getPassword())) {
                    $interface->addViolationAt('password', 'Wrong password');
                }
            })))))
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
                $request->getSession()->getFlashBag()->set('success', 'Password changed. Please log in again.');
                return $this->redirect($this->generateUrl('login'));
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
        }

        return array('form' => $form->createView(), 'title' => 'Change Password');
    }
}
