<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="main_page")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array('title' => "Welcome");
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request) {
    	$session = $request->getSession();

    	// $form = $this->createFormBuilder()
    	// 	->setAction($this->generateUrl('login_check'))
    	// 	->add('username', 'text')
    	// 	->add('password', 'password')
    	// 	->add('login', 'submit')
    	// 	->getForm();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        // 'last_username' => $session->get(SecurityContext::LAST_USERNAME)
        return array('error' => $error, 'title' => "Log In");
    }
}
