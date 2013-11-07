<?php

namespace Bio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Bio\UserBundle\Entity\User;
use Bio\UserBundle\Form\UserType;
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
        $user = new User();
        $form = $this->createForm(new UserType(), $user)
            ->add('submit', 'submit');

        return array(
            'form' => $form->createView(),
            'title' => 'Registered Users'
        );
    }

    /**
     * @Route("/{type}mote/{id}", name="mote_user", requirements={"type" = "de|pro"})
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function mote(Request $request, $type, User $entity = null) {

    	if ($entity && in_array('ROLE_SETUP', $entity->getRoles()) && $entity !== $this->getUser()) {
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
                return array('entities' => [$entity]);
            } catch (BioException $e) {
                return array('error' => 'Could not '.$type.'mote that user.');
            }
        } else {
            return array('error' => 'Cannot change permissions.');
        }
    }

    /**
     * @Route("/reset/{id}", name="reset_password")
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function resetAction(Request $request, AbstractUserStudent $user = null) {

        if (!$user) {
            return array('error' => 'Could not find user.');
        } else if ($user->getEmail() === '') {
           return array('error' => 'Cannot reset a password without an email.');
        } else if (in_array('ROLE_SETUP', $user->getRoles())){
            return array('error' => 'Cannot reset password.');
        } else {
            $encoder = $this->get('security.encoder_factory')->getEncoder($user);
            $pwd = substr(md5(rand()), 0, 7);
            $user->setPassword($encoder->encodePassword($pwd, $user->getSalt()));

            $this->getDoctrine()->getManager()->flush();

            $info = (new Database($this, 'BioInfoBundle:Info'))->findOne(array());;

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
            
            return array('entities' => [$user]);
        }
    }
}
