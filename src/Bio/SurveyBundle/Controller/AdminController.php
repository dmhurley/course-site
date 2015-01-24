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

use Bio\SurveyBundle\Type\SurveyType;


/**
 * @Route("/admin/survey")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="survey_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Survey');
    }

    /**
     * @Route("/manage", name="survey_manage")
     * @Template()
     */
    public function indexAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();
        $db = new Database($this, 'BioSurveyBundle:Survey');

        $survey = new Survey();
        $survey->setHidden(false);
        $survey->setAnonymous(true);

        $form = $this->createForm(new SurveyType(), $survey)
            ->add('submit', 'submit');

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            echo $survey->getQuestions()->count();

            if ($form->isValid()) {
                foreach ($survey->getQuestions() as $i => $question) {
                    $question->setSurvey($survey);
                }
                $db->add($survey);
                $db->close();
            } else {
                echo '<pre>'.$form->getErrorsAsString().'</pre>';
            }

        }

        $surveys = $db->find(array(), array(), false);

        return array(
            'form' => $form->createView(),
            'surveys' => $surveys,
            'title' => 'Manage Surveys'
        );
    }

    /**
     * @Route("/toggle/{id}", name="toggle_survey")
     */
    public function toggleSurveyAction(Request $request, Survey $survey = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($survey) {
            $survey->setHidden(!$survey->getHidden());
            $this->getDoctrine()->getManager()->flush();
            $flash->set('success', 'Survey ' . ($survey->getHidden() ? 'closed.' : 'opened.'));
        } else {
            $flash->set('failure');
        }

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('manage_surveys'));
        }
    }

    /**
     * @Route("/delete/{id}", name="delete_survey")
     */
    public function deleteSurveyAction(Request $request, Survey $survey = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($survey) {
            $db = new Database($this, 'BioSurveyBundle:Survey');
            $db->delete($survey);
            $db->close();
            $flash->set('success', 'Survey deleted.');
        } else {
            $flash->set('failure', 'Could not find that survey.');
        }

        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('manage_surveys'));
        }
    }

    /**
     * @Route("/download/{id}", name="download_survey")
     */
    public function downloadSurveyAction(Request $request, Survey $survey) {
        // build response
        $response = $this->render('BioSurveyBundle:Admin:download.xls.twig', array(
            'survey' => $survey
        ));

        $response->headers->set(
            "Content-Type", 'text/csv'
        );

        $response->headers->set(
            'Content-Disposition', ('attachment; filename="survey.xls"')
        );

        return $response;
    }
}
