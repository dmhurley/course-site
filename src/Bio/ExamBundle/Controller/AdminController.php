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
use Bio\ExamBundle\Entity\ExamGlobal;

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
     * @Route("/manage", name="manage_exams")
     * @Template()
     */
    public function examAction(Request $request)
    {
        $exam = new Exam();
    	$form = $this->get('form.factory')->createNamedBuilder('form', 'form', $exam)
    		->add('title', 'text', array('label'=>'Exam Name:'))
            ->add('section', 'text', array('label'=>'Section:', 'attr' => array('pattern' => '[A-Z]{1,2}', 'title' => 'One or two letter capitalized section name.')))
    		->add('tDate', 'date', array('label' => 'Test Date:'))
    		->add('tStart', 'time', array('label'=>'Test Start:'))
    		->add('tEnd', 'time', array('label'=>'Test End:'))
    		->add('tDuration', 'integer', array('label'=>'Test Length (m):'))
            ->add('gDate', 'date', array('label' => 'Grading Date:'))
            ->add('gStart', 'time', array('label'=>'Grading Start:'))
            ->add('gEnd', 'time', array('label'=>'Grading End:'))
            ->add('gDuration', 'integer', array('label'=>'Grade Length (m):'))
   			->add('add', 'submit')
   			->getForm();

        $db = new Database($this, 'BioExamBundle:ExamGlobal');
        $global = $db->findOne(array());
        $globalForm = $this->get('form.factory')->createNamedBuilder('global', 'form', $global)
            ->add('grade', 'integer', array('label' => "Tests To Grade:"))
            ->add('rules', 'textarea', array('label' => "Test Rules:"))
            ->add('set', 'submit')
            ->getForm();

   		$emptyForm = clone $form;

   		if ($request->getMethod() === "POST") {
            if ($request->request->has('form')) {
       			$form->handleRequest($request);
                try {
                    $this->checkDates($exam);
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
                    return $this->redirect($this->generateUrl('manage_exams'));
                }

       			if ($form->isValid()) {
       				$db->add($exam);
       			} else {
                    $request->getSession()->getFlashBag()->set('failure', 'Form is invalid.');
                }
                $form = $emptyForm;
            }

            if ($request->request->has('global')) {
                $globalForm->handleRequest($request);

                if ($form->isValid()) {
                    $dbGlobal = $db->findOne(array());

                    $dbGlobal->setGrade($global->getGrade())
                        ->setRules($global->getRules());
                }
            }

            try {
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Saved change.');
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Unable to save change.');
            }
   		}

        $db = new Database($this, 'BioExamBundle:Exam');
   		$exams = $db->find(array(), array('tDate' => 'ASC'), false);
    	return array('form' => $form->createView(), 'globalForm' => $globalForm->createView(), 'exams' => $exams, 'title' => 'Manage Exams');
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
    		return $this->redirect($this->generateUrl('manage_exams'));
    	}
    }

    /**
     * @Route("/edit", name="edit_exam")
     * @Template()
     */
    public function editAction(Request $request) {

    	$db = new Database($this, 'BioExamBundle:Exam');

    	if ($request->query->has('id')) {
    		$id = $request->query->get('id');
    		$exam = $db->findOne(array('id' => $id));
            if (!$exam) {
                $request->getSession()->getFlashBag()->set('failure', 'Unable to find that exam.');
                return $this->redirect($this->generateUrl('manage_exams'));
            }
    	} else {
            $request->getSession()->getFlashBag()->set('failure', 'No ID given.');
    		return $this->redirect($this->generateUrl('manage_exams'));
    	}

		$form = $this->createFormBuilder($exam)
			->add('title', 'text', array('label'=>'Exam Name:'))
            ->add('section', 'text', array('label'=>'Section:', 'attr' => array('pattern' => '[A-Z]{1,2}', 'title' => 'One or two letter capitalized section name.')))
    		->add('tDate', 'date', array('label' => 'Date:'))
    		->add('tStart', 'time', array('label'=>'Start Time:'))
    		->add('tEnd', 'time', array('label'=>'End Time:'))
    		->add('tDuration', 'integer', array('label'=>'Duration (m):'))
            ->add('gDate', 'date', array('label' => 'Grading Date:'))
            ->add('gStart', 'time', array('label'=>'Grading Start:'))
            ->add('gEnd', 'time', array('label'=>'Grading End:'))
            ->add('gDuration', 'integer', array('label'=>'Grade Length (m):'))
    		->add('questions', 'entity', array('class' => 'BioExamBundle:Question', 'property'=>'formattedQuestion', 'multiple' => true, 'expanded'=> true))
    		->add('id', 'hidden')
   			->add('edit', 'submit')
   			->getForm();

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);

             try {
                $this->checkDates($exam);
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
                return $this->redirect($this->generateUrl('edit_exam')."?id=".$request->query->get('id'));
            }

	   		if ($form->isValid()) {
	   			$dbExam = $db->findOne(array('id' => $exam->getId()));
	   			$dbExam->setTitle($exam->getTitle())
                    ->setSection($exam->getSection())
	   				->setTDate($exam->getTDate())
	   				->setTStart($exam->getTStart())
	   				->setTEnd($exam->getTEnd())
	   				->setTDuration($exam->getTDuration())
                    ->setGDate($exam->getGDate())
                    ->setGStart($exam->getGStart())
                    ->setGEnd($exam->getGEnd())
                    ->setGDuration($exam->getGDuration())
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

    	$db = new Database($this, 'BioExamBundle:Question');

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
    		return $this->redirect($this->generateUrl('manage_questions'));
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
     * @Template("BioExamBundle:Public:exam.html.twig")
     */
    public function previewAction(Request $request) {
        if ($request->query->get('id') && $request->query->get('type')) {
            $id = $request->query->get('id');
            $type = $request->query->get('type');

            $db = new Database($this, 'BioExamBundle:'.ucfirst($type));
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
     * @Template("BioExamBundle:Admin:download.txt.twig")
     */
    public function downloadAction(Request $request, $id) {
        $db = new Database($this, 'BioExamBundle:Exam');
        $exam = $db->findOne(array('id' => $id));

        $db = new Database($this, 'BioExamBundle:TestTaker');
        $takers = $db->find(array('exam' => $id), array('id' => 'ASC'), false);

        $db = new Database($this, 'BioExamBundle:ExamGlobal');
        $global = $db->findOne(array());

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$exam->getTitle().'.txt');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        echo "ExamID\t";
        echo "QuestionID\t";
        echo "AnswerID\t";
        echo "StudentID\t";
        echo "GraderID\t";
        echo "Name\t";
        echo "Section\t";
        echo "Did Grade\t";
        echo "grader score\t";
        echo "Time (elapsed)\t";
        echo "Time (s)\t";
        echo "Time Entered\t";
        echo "Time Scored\t";
        echo "Time Scored (s)\t";
        echo "Answer Count\t";
        echo "Grade Time (s)\t";
        echo "Answer\t";
        echo "Score\n";

        foreach ($takers as $taker) {
            foreach ($taker->getAnswers() as $answer) {
                foreach(array_keys($answer->getPoints()) as $key) {
                    echo $exam->getId()."\t";
                    echo $answer->getQuestion()->getId()."\t";
                    echo $answer->getId()."\t";
                    echo $taker->getStudent()->getSid()."\t";
                    echo $key."\t";
                    echo $taker->getStudent()->getLName().", ".$taker->getStudent()->getFName()."\t";
                    echo $taker->getStudent()->getSection()."\t";
                    echo (($count = count($taker->getGraded()) - $global->getGrade()) <= 0?"Yes":$count)."\t";
                    echo "0\t";
                    echo (($taker->getTimecard()[6]->getTimestamp() - $taker->getTimecard()[1]->getTimestamp())/60)."\t";
                    echo $taker->getTimecard()[6]->getTimestamp() - $taker->getTimecard()[1]->getTimestamp()."\t";
                    echo $taker->getTimecard()[4]->format("m-d-Y H:i:s")."\t";
                    echo "-\t";
                    echo "-\t";
                    echo count($taker->getAnswers())."\t";
                    echo "-\t";
                    echo $answer->getAnswer()."\t";
                    echo $answer->getPoints()[$key]."\n";
                }
            }
        }

        return array();
    }

    private function checkDates($exam){
        // exam checks
        if ($exam->getTStart() >= $exam->getTEnd()) {
            throw new BioException("Exam end cannot be before exam start.");
        }

        if (($exam->getTEnd()->getTimestamp() - $exam->getTStart()->getTimestamp())/60 < $exam->getGDuration() ) {
            throw new BioException("Exam window must be longer than exam duration.");
        }

        // grading checks
        if ($exam->getGStart() >= $exam->getGEnd()) {
            throw new BioException("Grading end cannot be before grading start.");
        }

        if (($exam->getGEnd()->getTimestamp() - $exam->getGStart()->getTimestamp())/60 < $exam->getGDuration() ) {
            throw new BioException("Grading window must be longer than grading duration.");
        }

        $testStart = new \DateTime($exam->getTDate()->format('Y-m-d ').$exam->getTStart()->format('H:i:s'));
        $testEnd = new \DateTime($exam->getTDate()->format('Y-m-d ').$exam->getTEnd()->format('H:i:s'));
        $gradeStart = new \DateTime($exam->getGDate()->format('Y-m-d ').$exam->getGStart()->format('H:i:s'));
        $gradeEnd = new \DateTime($exam->getGDate()->format('Y-m-d ').$exam->getGEnd()->format('H:i:s'));

       if ($gradeEnd <= $testEnd) {
            throw new BioException("Grading cannot end before exam ends.");
       }

       if (($gradeEnd->getTimestamp() - $testStart->getTimestamp())/60 < ($exam->getTDuration() + $exam->getGDuration())) {
            throw new BioException("Total window must be longer than total duration.");
       }
    }
}
