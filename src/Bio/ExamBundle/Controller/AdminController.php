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
    		->add('duration', 'integer', array('label'=>'Duration (m):'))
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

    /**
     * @Route("/delete", name="delete_exam")
     */
    public function deleteAction(Request $request) {
    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
    		$id = $request->query->get('id');

    		$db = new Database($this, 'BioExamBundle:Exam');
    		$exam = $db->findOne(array('id' => $id));
    		if ($exam) {
    			$db->delete($exam);
    			$db->close();
    			$request->getSession()->getFlashBag()->set('success', 'Exam deleted.');
    		} else {
    			$request->getSession()->getFlashBag()->set('failure', 'Could not find that exam.');
    		}
    	}

    	if ($request->headers->get('referer')){
    		return $this->redirect($request->headers->get('referer'));
    	} else {
    		return $this->redirect($this->generateUrl('view_exams'));
    	}
    }

    /**
     * @Route("/edit", name="edit_exam")
     * @Template()
     */
    public function editAction(Request $request) {

    	$db = new Database($this, 'BioExamBundle:Exam');

    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
    		$id = $request->query->get('id');

    		$exam = $db->findOne(array('id' => $id));
    	} else {
    		$exam = new Exam();
    	}

		$form = $this->createFormBuilder($exam)
			->add('name', 'text', array('label'=>'Exam Name:'))
    		->add('date', 'date', array('label' => 'Date:'))
    		->add('start', 'time', array('label'=>'Start Time:'))
    		->add('end', 'time', array('label'=>'End Time:'))
    		->add('duration', 'integer', array('label'=>'Duration (m):'))
    		->add('id', 'hidden')
   			->add('edit', 'submit')
   			->getForm();

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);
	   		if ($form->isValid()) {
	   			$dbExam = $db->findOne(array('id' => $exam->getId()));
	   			$dbExam->setName($exam->getName())
	   				->setDate($exam->getDate())
	   				->setStart($exam->getStart())
	   				->setEnd($exam->getEnd())
	   				->setDuration($exam->getDuration());

	   				$db->close();
	   				return $this->redirect($this->generateUrl('view_exams'));
	   		}
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Exam');
    }
}
