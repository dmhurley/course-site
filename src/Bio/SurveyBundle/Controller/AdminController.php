<?php

namespace Bio\SurveyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\SurveyBundle\Form\SurveyType;
use Bio\Survey\Entity\Survey;


/**
 * @Route("/admin/survey")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/manage", name="manage_surveys")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $flash = $request->getSession()->getFlashBag();
        $survey = new Survey();
        $form = $this->get('form.factory')->createNamed('form', new SurveyType(), $survey)
            ->add('submit', 'submit');

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $db->close();
                    $flash->set('success', 'Created survey.');
                    return $this->redirect($this->generateUrl('manage_surveys'));
                }
            }
        }

        $db = new Database($this, 'BioSurveyBundle:Survey');
        $surveys = $db->find(array(), array(), false);

        return array(
            'form' => $form->createView(),
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
            $db->delete($exam);
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
