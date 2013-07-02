<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/course")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="edit_info")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array('title' => "Edit Course Information");
    }
}
