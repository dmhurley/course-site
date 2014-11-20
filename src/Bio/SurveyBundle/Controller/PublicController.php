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
    public function takeAction(Request $request, Survey $survey = null) {
        $flash = $request->getSession()->getFlashBag();
        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository('BioSurveyBundle:Survey');

        if ($survey === null) {
            $flash->set('failure', 'Survey does not exist.');
            if ($request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            } else {
                return $this->redirect($this->generateUrl('view_surveys'));
            }
        }

        if ($repo->hasTaken($survey, $user)) {
            $flash->set('failure', 'Survey already taken.');
            return $this->redirect(
                $this->generateUrl('review_survey', array('id' => $survey->getId()))
            );
        }


        return array(
            'title' => $survey->getName(),
            'survey' => $survey
        );
    }

    /**
     * @Route("/review/{id}", name="review_survey")
     * @Template()
     */
    public function reviewAction(Request $request, Survey $survey = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($survey === null) {
            $flash->set('failure', 'Survey does not exist.');
            if ($request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            } else {
                return $this->redirect($this->generateUrl('view_surveys'));
            }
        }

        if (!$repo->hasTaken($survey, $user)) {
            $flash->set('failure', 'Survey already taken.');
            return $this->redirect(
                $this->generateUrl('take_survey', array('id' => $survey->getId()))
            );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository('BioSurveyBundle:Survey');

        return array(
            'title': $survey->getName();
        )
    }
}
