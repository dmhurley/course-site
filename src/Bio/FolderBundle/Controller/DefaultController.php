<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;

	/**
     * @Route("/folders")
     * @Template()
     */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="view_folders")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array('title' => "View Folders");
    }
}
