<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;

use Bio\DataBundle\Objects\Database;
use Bio\UserBundle\Entity\User;
use Bio\InfoBundle\Entity\Announcement;


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
            $request->getSession()->getFlashBag()->set('failure', "Incorrect username or password.");
        }

        return array('title' => "Log In", 'last_username' => $session->get(SecurityContext::LAST_USERNAME));
    }

    /**
     * @Route("/register", name="register")
     * @Template()
     */
    public function registerAction(Request $request) {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('username', 'text', array('label' => 'Username:'))
            ->add('password', 'password', array('label' => 'Password:'))
            ->add('password1', 'password', array('mapped' => false, 'label' => 'Password:'))
            ->add('register', 'submit')
            ->getForm();

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);
                if ($form->get('password')->getData() !== $form->get('password1')->getData()) {
                     $request->getSession()->getFlashBag()->set('failure', 'You typed in two different passwords.');
                } else if ($form->isValid()) {
                    $db = new Database($this, 'BioUserBundle:User');
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $pwd = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                    $user->setPassword($pwd);
                    $user->setRoles(array('ROLE_USER'));

                    $db->add($user);
                    $db->close();
                } else {
                    $request->getSession()->getFlashBag()->set('failure', 'Form was invalid.');
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

    public function emailAction() {
        $db = new Database($this, 'BioInfoBundle:Info');
        $instructor = $db->findOne(array());
        $link = 'mailto:';
        $link.=$instructor->getEmail();
        return new Response($link);
    }
}
