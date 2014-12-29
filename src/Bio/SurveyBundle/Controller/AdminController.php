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
 * @Route("/admin/survey")
 */
class AdminController extends Controller
{
    /**
     * @Route("/manage", name="manage_surveys")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $flash = $request->getSession()->getFlashBag();
        $survey = new Survey();
        $db = new Database($this, 'BioSurveyBundle:Survey');

        if ($request->getMethod() === "POST") {
            $data = $request->request->get('form');
            foreach ($data as $key => $value) {
                if (is_numeric($key)) {
                    $question = new SurveyQuestion();
                    $question->setType(
                        count($value) === 1 ? "response" : "multiple"
                    );
                    $question->setSurvey($survey);
                    $question->setData($value);
                    $survey->addQuestion($question);
                }
            }
            $survey->setName($data['name']);

            try {
                $db->add($survey);
                $db->close();
                $flash->set('success', 'Survey added.');
            } catch(\Exception $e) {
                $flash->set('failure', 'Survey could not be added.');
            }
        }

        $surveys = $db->find(array(), array(), false);

        return array(
            'surveys' => $surveys,
            'title' => 'Manage Surveys'
        );
    }

    /**
     * @Route("/delete/{id}", name="delete_survey")
     */
    public function deleteSurveyAction(Request $request, Survey $survey) {
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
     * @Route("/delete/{id}", name="download_survey")
     */
    public function downloadSurveyAction(Request $request, Survey $survey) {

    }
}
