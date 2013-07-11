<?php

namespace Bio\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
/**
 * @Route("/admin/user")
 */
class DefaultController extends Controller
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
     * @Route("/{type}mote", name="mote_user")
     */
    public function mote(Request $request, $type) {
    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $db = new Database($this, 'BioUserBundle:User');

            $entity = $db->findOne(array('id'=>$id));
            $role = $entity->getRoles()[0];

            if ($type==="de") {
            	if ($role === "ROLE_USER") {
            		$db->delete($entity);
            	} else if ($role === "ROLE_ADMIN") {
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

            $db->close();
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
        }
    }

    /**
     * @Route("/delete", name="delete_user")
     */
    public function delete(Request $request) {
    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $db = new Database($this, 'BioUserBundle:User');

            $entity = $db->findOne(array('id'=>$id));
            if ($entity) {
                $db->delete($entity);
                $db->close();
                $request->getSession()->getFlashBag()->set('success '.$entity->getUsername().' deleted.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that user.');
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_users'));
        }

    }
}
