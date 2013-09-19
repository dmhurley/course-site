<?php

namespace Bio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Bio\UserBundle\Entity\User;
use Bio\UserBundle\Entity\AbstractUserStudent;

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
    public function mote(Request $request, $type, User $entity = null) {
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
                $request->getSession()->getFlashBag()->set('success', ucfirst($type)."moted '".$entity->getUserName()."'.");
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Could not '.$type.'mote that user.');
            }
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find that user.');
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
    public function delete(Request $request, User $entity = null) {
        if ($entity && $entity->getRoles()[0] !== 'ROLE_SETUP' && $entity !== $this->getUser()) {
            $db = new Database($this, 'BioUserBundle:User');
            $db->delete($entity);
            $db->close();
            $request->getSession()->getFlashBag()->set('success', "Deleted '".$entity->getUsername()."'.");
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find that user.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_users'));
        }

    }

    /**
     * @Route("/reset/{id}", name="reset_password")
     */
    public function resetAction(Request $request, AbstractUserStudent $user = null) {
        if ($user) {
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
                ->setBody('Your new password for '. $info->getTitle() .' is <code>'. $pwd .'</code>. Please sign in to change it.');

            $this->get('mailer')->send($message);
            $request->getSession()->getFlashBag()->set('success', 'Password reset.');
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find user.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('main_page'));
        }
    }
}
