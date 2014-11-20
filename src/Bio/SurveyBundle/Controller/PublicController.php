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
        $flash = $request->getSession()->getFlashBag();
        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('BioSurveyBundle:Survey');

        $openSurveys = $repo->getOpenSurveys($user);
        $finishedSurveys = $repo->getFinishedSurveys($user);

        return array(
            'title' => 'Your Surveys',
            'surveys' => $openSurveys,
            'completed' => $finishedSurveys
        );
    }

    /**
     * @Route("/{id}", name="take_survey")
     * @Template()
     */
    public function takeAction(Request $request, Survey $survey) {

    }
}
