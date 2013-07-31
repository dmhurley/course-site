<?php

namespace Bio\NewExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\NewExamBundle\Entity\Exam;
use Bio\NewExamBundle\Entity\Question;

/**
 * @Route("/admin/newexam")
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
     * @Route("/manage", name="manage_exams")
     * @Template()
     */
    public function examAction(Request $request)
    {
        $exam = new Exam();
    	$form = $this->createFormBuilder($exam)
    		->add('title', 'text', array('label'=>'Exam Name:'))
    		->add('date', 'date', array('label' => 'Date:'))
    		->add('start', 'time', array('label'=>'Start Time:'))
    		->add('end', 'time', array('label'=>'End Time:'))
    		->add('duration', 'integer', array('label'=>'Duration (m):'))
   			->add('add', 'submit')
   			->getForm();

   		$emptyForm = clone $form;

   		$db = new Database($this, 'BioNewExamBundle:Exam');

   		if ($request->getMethod() === "POST") {
   			$form->handleRequest($request);

   			if ($form->isValid()) {
   				$db->add($exam);
                try {
   				    $db->close();
                    $request->getSession()->getFlashBag()->set('success', 'Exam added.');
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', 'Unable to save exam.');
                }
   			} else {
                $request->getSession()->getFlashBag()->set('failure', 'Form is invalid.');
            }
            $form = $emptyForm;
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

    		$db = new Database($this, 'BioNewExamBundle:Exam');
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
    		return $this->redirect($this->generateUrl('manage_exams'));
    	}
    }

    /**
     * @Route("/edit", name="edit_exam")
     * @Template()
     */
    public function editAction(Request $request) {

    	$db = new Database($this, 'BioNewExamBundle:Exam');

    	if ($request->getMethod() === "GET" && $request->query->get('id')) {
    		$id = $request->query->get('id');
    		$exam = $db->findOne(array('id' => $id));
            if (!$exam) {
                $request->getSession()->getFlashBag()->set('failure', 'Unable to find that exam.');
                return $this->redirect($this->generateUrl('manage_exams'));
            }
    	} else {
    		$exam = new Exam();
    	}

		$form = $this->createFormBuilder($exam)
			->add('title', 'text', array('label'=>'Exam Name:'))
    		->add('date', 'date', array('label' => 'Date:'))
    		->add('start', 'time', array('label'=>'Start Time:'))
    		->add('end', 'time', array('label'=>'End Time:'))
    		->add('duration', 'integer', array('label'=>'Duration (m):'))
    		->add('questions', 'entity', array('class' => 'BioNewExamBundle:Question', 'property'=>'formattedQuestion', 'multiple' => true, 'expanded'=> true))
    		->add('id', 'hidden')
   			->add('edit', 'submit')
   			->getForm();

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);

	   		if ($form->isValid()) {
	   			$dbExam = $db->findOne(array('id' => $exam->getId()));
	   			$dbExam->setTitle($exam->getTitle())
	   				->setDate($exam->getDate())
	   				->setStart($exam->getStart())
	   				->setEnd($exam->getEnd())
	   				->setDuration($exam->getDuration())
	   				->setQuestions($exam->getQuestions());
                    try {
	   				    $db->close();
                        $request->getSession()->getFlashBag()->set('success', 'Exam edited.');
                    } catch (BioException $e) {
                        $request->getSession()->getFlashBag()->set('failure', 'Unable to save changes.');
                    }
	   				return $this->redirect($this->generateUrl('manage_exams'));
	   		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Exam');
    }

    /**
     * @Route("/questions/manage", name="manage_questions")
     * @Template()
     */
    public function questionAction(Request $request) {
    	$q = new Question();
    	$form = $this->createFormBuilder($q)
    		->add('question', 'textarea', array('label' => 'Question:', 'attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('answer', 'textarea', array('label' => 'Answer/Rubric:','attr' => array('class' => 'tinymce', 'data-theme' => 'bio')))
    		->add('points', 'integer', array('label' => 'Points:'))
            ->add('tags', 'text', array('label' => 'Tags:', 'mapped' => false, 'required' => false, 'attr' => array('pattern' => '[a-z\s]+', 'title' => 'Lower case tags seperated by spaces. a-z only.')))
    		->add('add', 'submit')
    		->getForm();

    	$emptyForm = clone $form;

    	$db = new Database($this, 'BioNewExamBundle:Question');

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
                $q->setTags(explode(" ", $form->get('tags')->getData()));
    			$db->add($q);
                try {
                    $db->close();
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', 'Unable to add question.');
                }
    		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
            $form = $emptyForm;
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

    		$db = new Database($this, 'BioNewExamBundle:Question');
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
    		return $this->redirect($this->generateUrl('manage_questions'));
    	}
    }

    /**
     * @Route("/questions/edit", name="edit_question")
     * @Template()
     */
    public function editQuestionAction(Request $request) {

    	$db = new Database($this, 'BioNewExamBundle:Question');

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
            ->add('tags', 'text', array('label' => 'Tags:', 'data' => implode(' ', $q->getTags()), 'mapped' => false, 'required' => false, 'attr' => array('pattern' => '[a-z\s]+', 'title' => 'Lower case tags seperated by spaces. a-z only.')))
    		->add('id', 'hidden')
   			->add('edit', 'submit')
   			->getForm();

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);
	   		if ($form->isValid()) {
	   			$dbQ = $db->findOne(array('id' => $form->get('id')->getData()));
	   			$dbQ->setQuestion($q->getQuestion())
	   				->setAnswer($q->getAnswer())
	   				->setPoints($q->getPoints())
                    ->setTags(explode(' ', $form->get('tags')->getData()));
                    try {
	   				    $db->close();
                        $request->getSession()->getFlashBag()->set('success', 'Question edited.');
                    } catch (BioException $e) {
                        $request->getSession()->getFlashBag()->set('failure', 'Unable to save changes.');
                    }
	   				return $this->redirect($this->generateUrl('manage_questions'));
	   		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Questions');
    }

    /**
     * @Route("/preview", name="preview")
     * @Template("BioNewExamBundle:Public:exam.html.twig")
     */
    public function previewAction(Request $request) {
        if ($request->query->get('id') && $request->query->get('type')) {
            $id = $request->query->get('id');
            $type = $request->query->get('type');

            $db = new Database($this, 'BioNewExamBundle:'.ucfirst($type));
            $entity = $db->findOne(array('id' => $id));

            if ($entity) {
                if ($type == 'question') {
                    $exam = array();
                    $exam['questions'] = array($entity);
                } else {
                    $exam = $entity;
                }

                return array('exam' => $exam, 'title' => 'Preview');
            }
            return $this->redirect($this->generateUrl('view_'.$type.'s'));
        }
        return $this->redirect($this->generateUrl('manage_exams'));
    }

    /**
     * @Route("/download/{id}", name="download_exam")
     * @Template("BioFolderBundle:Download:download.html.twig")
     */
    public function downloadAction(Request $request, $id) {
        $db = new Database($this, 'BioExamBundle:Exam');
        $exam = $db->findOne(array('id' => $id));
        $db = new Database($this, 'BioExamBundle:TestTaker');
        $takers = $db->find(array('exam' => $id), array(), false);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$exam->getName().'.txt');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        return array('text' => '');
    }
}
