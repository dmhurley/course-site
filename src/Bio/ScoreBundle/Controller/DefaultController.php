<?php

namespace Bio\ScoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/scores", name="scores")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array('title' => 'Scores');
    }
}
