<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/clicker")
 * @Template()
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="register_clicker")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        return array('title' => "Register Clicker");
    }
}
