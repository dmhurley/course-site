<?php

namespace Bio\ExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\ExamBundle\Entity\Exam;

/**
 * @Route("/admin/exam")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="exam_instruct")
     * @Template()
     */
    public function instructionAction()
    {
        return array('title' => "Practice Exams");
    }

    /**
     * @Route("/view", name="view_exams")
     * @Template()
     */
    public function examsAction(Request $request) {
    	$exam = new Exam();
    	$form = $this->createFormBuilder($exam)
    		->add('name', 'text', array('label'=>'Exam Name:'))
    		->add('date', 'date', array('label' => 'Date:'))
    		->add('start', 'time', array('label'=>'Start Time:'))
    		->add('end', 'time', array('label'=>'End Time:'))
    		->add('duration', 'integer', array('label'=>'Duration (minutes):'))
   			->add('add', 'submit')
   			->getForm();

   		$db = new Database($this, 'BioExamBundle:Exam');

   		if ($request->getMethod() === "POST") {
   			$form->handleRequest($request);

   			if ($form->isValid()) {
   				$db->add($exam);
   				$db->close();
   			}
   		}

   		$exams = $db->find(array(), array('date' => 'ASC'), false);
    	return array('form' => $form->createView(), 'exams' => $exams, 'title' => 'Manage Exams');
    }
}
