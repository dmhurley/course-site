<?php

namespace Bio\SurveyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\SurveyBundle\Entity\Survey;
use Bio\SurveyBundle\Entity\SurveyQuestion;


/**
 * @Route("/survey")
 */
class PublicController extends Controller
{

    /**
     * @Route("/", name="view_surveys")
     * @Template()
     */
    public function indexAction(Request $request) {

    }

    /**
     * @Route("/{id}", name="take_survey")
     * @Template()
     */
    public function takeAction(Request $request, Survey $survey) {

    }
}
