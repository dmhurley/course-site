<?php

namespace Bio\SwitchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/switch/requests", name="switch_requests")
     * @Template()
     */
    public function requestsAction(Request $request)
    {
        return array( 'title' => 'Requests');
    }
}
