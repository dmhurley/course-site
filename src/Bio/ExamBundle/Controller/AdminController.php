<?php

namespace Bio\ExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\ExamBundle\Entity\Exam;
use Bio\ExamBundle\Entity\Question;

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
     * @Route("/manage", name="view_exams")
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
    		->add('questions', 'entity', array('label' => 'Questions:', 'class' => 'BioExamBundle:Question', 'property'=>'formattedQuestion', 'multiple' => true, 'expanded'=> true))
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
	   				->setDuration($exam->getDuration())
	   				->setQuestions($exam->getQuestions());

	   				$db->close();
	   				// return $this->redirect($this->generateUrl('view_exams'));
	   		}
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Exam');
    }

    /**
     * @Route("/questions", name="view_questions")
     * @Template()
     */
    public function questionAction(Request $request) {
    	$q = new Question();
    	$form = $this->createFormBuilder($q)
    		->add('question', 'textarea', array('attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('answer', 'textarea', array('attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('points', 'integer')
    		->add('add', 'submit')
    		->getForm();

    	$db = new Database($this, 'BioExamBundle:Question');

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$db->add($q);
    			$db->close();http://localhost/~nick/course-site/web/app_dev.php/
    		}
    	}

    	$questions = $db->find(array(), array(), false);
    	return array('form' => $form->createView(), 'questions' => $questions, 'title' => 'Questions');
    }

    /**
     * @Route("/questions/delete", name="delete_question")
     */
    public function deleteQuestionAction(Request $request) {
    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
    		$id = $request->query->get('id');

    		$db = new Database($this, 'BioExamBundle:Question');
    		$q = $db->findOne(array('id' => $id));
    		if ($q) {
    			$db->delete($q);
    			$db->close();
    			$request->getSession()->getFlashBag()->set('success', 'Question deleted.');
    		} else {
    			$request->getSession()->getFlashBag()->set('failure', 'Could not find that question.');
    		}
    	}

    	if ($request->headers->get('referer')){
    		return $this->redirect($request->headers->get('referer'));
    	} else {
    		return $this->redirect($this->generateUrl('view_questions'));
    	}
    }

    /**
     * @Route("/questions/edit", name="edit_question")
     * @Template()
     */
    public function editQuestionAction(Request $request) {

    	$db = new Database($this, 'BioExamBundle:Question');

    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
    		$id = $request->query->get('id');

    		$q = $db->findOne(array('id' => $id));
    	} else {
    		$q = new Question();
    	}

		$form = $this->createFormBuilder($q)
			->add('question', 'textarea', array('attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('answer', 'textarea', array('attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('points', 'integer')
    		->add('id', 'hidden')
   			->add('edit', 'submit')
   			->getForm();

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);
	   		if ($form->isValid()) {
	   			$dbQ = $db->findOne(array('id' => $form->get('id')->getData()));
	   			$dbQ->setQuestion($q->getQuestion())
	   				->setAnswer($q->getAnswer())
	   				->setPoints($q->getPoints());

	   				$db->close();
	   				return $this->redirect($this->generateUrl('view_questions'));
	   		}
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Questions');
    }
}
