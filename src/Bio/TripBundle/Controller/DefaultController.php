<?php

namespace Bio\TripBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/** 
 * @Route("/admin/trip")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/manage", name="manage_trips")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array( 'title' => "Manage Trips");
    }
}
