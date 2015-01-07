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
use Bio\SurveyBundle\Entity\SurveyTaker;
use Bio\SurveyBundle\Entity\SurveyAnswer;
use Bio\SurveyBundle\Type\SurveyAnswerType;


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
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioSurveyBundle:Survey');

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

        $taker = new SurveyTaker();
        $taker->setSurvey($survey)
            ->setStudent($user);

        foreach($survey->getQuestions() as $question) {
            $answer = new SurveyAnswer();
            $answer->setQuestion($question)
                ->setSurveyTaker($taker);

            $taker->addAnswer($answer);
        }

        $form = $this->createFormBuilder($taker)
            ->add('answers', 'collection', array(
                'type' => new SurveyAnswerType()
            ))
            ->add('submit', 'submit')
            ->getForm();


        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {

                try {
                    $em->persist($taker);
                    $em->flush();
                    $flash->set('success', 'Answers saved.');

                    return $this->redirect(
                        $this->generateUrl('review_survey', array(
                            'id' => $survey->getId()
                        ))
                    );
                } catch (Exception $e) {
                    $flash->set('failure', 'Unable to save answers.');
                }
            } else {
                $flash->set('failure', 'Invalid answer(s).');
            }
        }

        return array(
            'title' => $survey->getName(),
            'survey' => $survey,
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/review/{id}", name="review_survey")
     * @Template()
     */
    public function reviewAction(Request $request, Survey $survey = null) {
        $flash = $request->getSession()->getFlashBag();
        $repo = $this->getDoctrine()->getManager()->getRepository('BioSurveyBundle:Survey');
        $user = $this->get('security.context')->getToken()->getUser();

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

        $taker = $repo->getTaker($survey, $user);

        return array(
            'title' => $survey->getName(),
            'taker' => $taker
        );
    }
}
