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
use Bio\ExamBundle\Form\ExamType;
use Bio\ExamBundle\Form\ExamGlobalType;
use Bio\ExamBundle\Form\QuestionType;

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
        $flash = $request->getSession()->getFlashBag();

        $exam = new Exam();
    	$form = $this->get('form.factory')->createNamed('form', new ExamType(), $exam)
            ->add('submit', 'submit');

        $db = new Database($this, 'BioExamBundle:ExamGlobal');
        $global = $db->findOne(array());
        $globalForm = $this->get('form.factory')->createNamed('global', new ExamGlobalType(), $global)
            ->add('set', 'submit');

   		if ($request->getMethod() === "POST") {
            $isValid = true;

            if ($request->request->has('form')) {
       			$form->handleRequest($request);

       			if ($form->isValid()) {
       				$db->add($exam);
       			} else {
                    $isValid = false;
                }
            }

            if ($request->request->has('global')) {
                $globalForm->handleRequest($request);

                if (!$globalForm->isValid()) {
                     $isValid = false;
                }
            }

            if ($isValid) {
                try {
                    $db->close();
                    $flash->set('success', 'Saved change.');
                    return $this->redirect($this->generateUrl('manage_exams'));
                } catch (BioException $e) {
                    $flash->set('failure', 'Unable to save change.');
                }
            } else {
                $flash->set('failure', 'Invalid form.');
            }
   		}

        $db = new Database($this, 'BioExamBundle:Exam');
   		$exams = $db->find(array(), array('tDate' => 'ASC'), false);

    	return array(
            'form' => $form->createView(),
            'globalForm' => $globalForm->createView(),
            'exams' => $exams,
            'title' => 'Manage Exams'
            );
    }

    /**
     * @Route("/delete/{id}", name="delete_exam")
     */
    public function deleteAction(Request $request, Exam $exam = null) {
        $flash = $request->getSession()->getFlashBag();

		if ($exam) {
            $db = new Database($this, 'BioExamBundle:Exam');
			$db->delete($exam);
			$db->close();
			$flash->set('success', 'Exam deleted.');
		} else {
			$flash->set('failure', 'Could not find that exam.');
		}

    	if ($request->headers->get('referer')){
    		return $this->redirect($request->headers->get('referer'));
    	} else {
    		return $this->redirect($this->generateUrl('manage_exams'));
    	}
    }

    /**
     * @Route("/edit/{id}", name="edit_exam")
     * @Template()
     */
    public function editAction(Request $request, Exam $exam = null) {
        $flash = $request->getSession()->getFlashBag();
        if (!$exam) {
            $flash->set('failure', 'Unable to find that exam.');
            return $this->redirect($this->generateUrl('manage_exams'));
        }

		$form = $this->createForm(new ExamType(), $exam)
            ->add('questions', 'entity', array(
                'class' => 'BioExamBundle:Question',
                'property'=>'formattedQuestion',
                'multiple' => true,
                'expanded'=> true
                )
            )
            ->add('save', 'submit');

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);

	   		if ($form->isValid()) {
                    $db = new Database($this, 'BioExamBundle:Exam');

                    try {
	   				    $db->close();
                        $flash->set('success', 'Exam edited.');
                    } catch (BioException $e) {
                        $flash->set('failure', 'Unable to save changes.');
                    }
	   				return $this->redirect($this->generateUrl('manage_exams'));
	   		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Exam');
    }

    /**
     * @Route("/questions/manage", name="manage_questions")
     * @Template()
     */
    public function questionAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

    	$q = new Question();
    	$form = $this->createForm(new QuestionType(), $q)
            ->add('submit', 'submit');

    	$db = new Database($this, 'BioExamBundle:Question');

    	if ($request->getMethod() === "POST") {

    		$form->handleRequest($request);

    		if ($form->isValid()) {
                $q->setTags(explode(" ", $form->get('tags')->getData()));
    			$db->add($q);
                try {
                    $db->close();
                    $flash->set('success', 'Added question.');
                    return $this->redirect($this->generateUrl('manage_questions'));
                } catch (BioException $e) {
                    $flash->set('failure', 'Unable to add question.');
                }
    		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	$questions = $db->find(array(), array(), false);
    	return array(
            'form' => $form->createView(),
            'questions' => $questions,
            'title' => 'Questions'
            );
    }

    /**
     * @Route("/questions/delete/{id}", name="delete_question")
     */
    public function deleteQuestionAction(Request $request, Question $q = null) {
        $flash = $request->getSession()->getFlashBag();
		if ($q) {
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();

            $query = $qb->select('e')
                ->from('BioExamBundle:Exam', 'e')
                ->where(':q MEMBER OF e.questions')
                ->setParameter('q', $q)
                ->getQuery();
            $result = $query->getResult();

            if (count($result) > 0) {
                $flash->set('failure', 'That question is used in an Exam.');
            } else {
                $db = new Database($this, 'BioExamBundle:Question');
    			$db->delete($q);
    			$db->close();
    			$flash->set('success', 'Question deleted.');
            }
		} else {
			$flash->set('failure', 'Could not find that question.');
		}

    	if ($request->headers->get('referer')){
    		return $this->redirect($request->headers->get('referer'));
    	} else {
    		return $this->redirect($this->generateUrl('manage_questions'));
    	}
    }

    /**
     * @Route("/questions/edit/{id}", name="edit_question")
     * @Template()
     */
    public function editQuestionAction(Request $request, Question $q = null) {
        $flash = $request->getSession()->getFlashBag();

		$form = $this->createForm(new QuestionType(), $q)
            ->add('submit', 'submit');

    	if ($request->getMethod() === "POST") {
	   		$form->handleRequest($request);
	   		if ($form->isValid()) {
                $db = new Database($this, 'BioExamBundle:Question');
                try {
                    $q->setTags(explode(" ", $form->get('tags')->getData()));
   				    $db->close();
                    $flash->set('success', 'Question edited.');
                } catch (BioException $e) {
                    $flash->set('failure', 'Unable to save changes.');
                }
   				return $this->redirect($this->generateUrl('manage_questions'));
	   		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Edit Questions');
    }

    /**
     * @Route("/preview", name="preview")
     * @Template()
     */
    public function previewAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

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
            } else {
                $flash->set('failure', 'Could not find that '.$type.'.');
                return $this->redirect($this->generateUrl('manage_'.$type.'s'));
            }
        }
        return $this->redirect($this->generateUrl('manage_exams'));
    }

    /**
     * @Route("/download/{id}", name="download_exam")
     */
    public function downloadAction(Request $request, Exam $exam) {
        if (!$exam) {
            $request->getSession()->getFlashBag()->set('failure', 'Exam does not exist.');
            return $this->redirect($this->generateUrl('manage_exams'));
        }

        // get all test takers from exam, get global settings
        $db = new Database($this, 'BioExamBundle:TestTaker');
        $takers = $db->find(array('exam' => $exam->getId()), array('id' => 'ASC'), false);
        $db = new Database($this, 'BioExamBundle:ExamGlobal');
        $global = $db->findOne(array());


        // create header
        $responseText = [
            /******* COLUMN NAMES *******/
            "ExamID\t".            // guaranteed
            "QuestionID\t".        // guaranteed
            "AnswerID\t".          // guaranteed?
            "StudentID\t".         // guaranteed
            "GraderID\t".          // guaranteed
            "Name\t".              // guaranteed
            "Section\t".           // guaranteed
            "Did Grade\t".         // guaranteed
            "grader score\t".      // 0
            "Time (elapsed)\t".    // possible to not finish (status 6)
            "Time (s)\t".          // ..
            "Time Entered\t".      // possible to not submit scores (status 4)
            "Time Scored\t".       // can only estimate
            "Time Scored (s)\t".   // can only estimate
            "Answer Count\t".      // ?
            "Grade Time (s)\t".    // what?
            "Answer\t".            // didn't have to submit
            "Score\t".             // didn't have to be graded
            "Total Mean\t".
            "Comment"
        ];

        /******* DATA *******/
        $examID = $exam->getId();
        foreach ($takers as $taker) {   // status >= 1
            if ($taker->getStatus() === 1) {
                continue;
            }

            /**** TAKER DATA ****/
            $name = $taker->getStudent()->getLName().", ".$taker->getStudent()->getFName().($taker->getStudent()->getMName()?' '.$taker->getStudent()->getMName():'');
            $studentID = $taker->getStudent()->getSid();
            $studentEmail = $taker->getStudent()->getEmail();
            $section = $taker->getStudent()->getSection()->getName();
            $didGrade = $taker->getStatus() === 5 || count($taker->getGraded()) >= $global->getGrade()?"Yes":count($taker->getGraded());

            if ($taker->getStatus() >= 3) {
                $timeElapsedSeconds = $taker->getTimestamp('submitted')[0]['time']->getTimestamp() - $taker->getTimestamp('started')[0]['time']->getTimestamp();
                $timeElapsedMinutes = $timeElapsedSeconds/60;
                $timeEntered = $taker->getTimestamp('submitted')[0]['time']->format("m-d-Y H:i:s");
            } else {
                $timeElapsedSeconds = "";
                $timeElapsedMinutes = "";
                $timeEntered = "";
            }

            $answerCount = count($taker->getAnswers());
            /**** END TAKER DATA ****/

            // if the person never submitted their test, return now with lots of things blank
            if ($taker->getStatus() < 3) {
                $responseText[] = $this->echoArray(
                    array(
                        $examID,                    // exam id
                        '',                         // question id
                        '',                         // answer id
                        $studentID,                 // student id
                        '',                         // grader id
                        $name,                      // name
                        $section,                   // section
                        $didGrade,                  // did grade
                        0,                          // grader score (always 0)
                        $timeElapsedMinutes,        // time elapsed
                        $timeElapsedSeconds,        // time (s)
                        $timeEntered,               // time entered
                        '',                         // time scored
                        '',                         // time scored (s)
                        $answerCount,               // answer count
                        '',                         // grade time
                        '',                         // answer
                        0,                          // score
                        0,                          // total mean score
                        '',                         // comment
                        $studentEmail               // email
                    )
                );
            } else {
                foreach ($taker->getAnswers() as $answer) { // status >= 3

                    /**** ANSWER DATA ****/
                    $answerText = str_replace(array("\n", "\t", "\r\n", "\n\r", "\r"), ' ',$answer->getAnswer());
                    $answerID = $answer->getId();
                    $questionID = $answer->getQuestion()->getId();
                    /**** END ANSWER DATA ****/

                    // if the person finished their test but was never graded
                    if (count($taker->getGradedBy()) === 0) {
                        $responseText[] = $this->echoArray(
                            array(
                                $examID,                    // exam id
                                $questionID,                // question id
                                $answerID,                  // answer id
                                $studentID,                 // student id
                                '',                         // grader id
                                $name,                      // name
                                $section,                   // section
                                $didGrade,                  // did grade
                                0,                          // grader score (always 0)
                                $timeElapsedMinutes,        // time elapsed
                                $timeElapsedSeconds,        // time (s)
                                $timeEntered,               // time entered
                                '',                         // time scored
                                '',                         // time scored (s)
                                $answerCount,               // answer count
                                '',                         // grade time
                                $answerText,                // answer
                                "",                         // score
                                'NOT GRADED',               // total mean score
                                '',                         // comment
                                $studentEmail               // email
                            )
                        );
                    } else {

                        /**** POINTS DATA ****/
                        $totalMean = 0;
                        foreach($taker->getAnswers() as $a) {
                            $average = 0;
                            $realGraders = 0;
                            foreach($a->getGrades() as $g) {
                                if ($g->getPoints() !== null) {
                                    $average += $g->getPoints();
                                    $realGraders++;
                                }
                            }
                            $totalMean+= $average/$realGraders;
                        }
                        /**** END POINTS DATA ****/

                        foreach($answer->getGrades() as $grade) { // status >= 4

                            /**** GRADE DATA ****/
                            $graderID = $grade->getGrader()?$grade->getGrader()->getStudent()->getSid():'dropped';
                            if ($grade->getEnd() !== null){
                                $timeScoredSeconds = strtotime($grade->getEnd()->format("H:i:s")) - strtotime($exam->getGDate()->format("Y-m-d"));
                                $timeScoredMinutes = $timeScoredSeconds/60;
                                $gradeTime = $grade->getEnd()->getTimestamp() - $grade->getStart()->getTimestamp();
                            } else {
                                $timeScoredSeconds = "";
                                $timeScoredMinutes = "";
                                $gradeTime = "";
                            }

                            $comment = str_replace(array("\n", "\t", "\r\n", "\n\r", "\r"), ' ', $grade->getComment());

                            if ($grade->getPoints() !== null) {
                                $points = $grade->getPoints();
                            } else {
                                $points = '';
                            }
                            /**** END GRADE DATA ****/

                            $responseText[] = $this->echoArray(
                                array(
                                    $examID,                    // exam id
                                    $questionID,                // question id
                                    $answerID,                  // answer id
                                    $studentID,                 // student id
                                    $graderID,                  // grader id
                                    $name,                      // name
                                    $section,                   // section
                                    $didGrade,                  // did grade
                                    0,                          // grader score (always 0)
                                    $timeElapsedMinutes,        // time elapsed
                                    $timeElapsedSeconds,        // time (s)
                                    $timeEntered,               // time entered
                                    $timeScoredMinutes,         // time scored
                                    $timeScoredSeconds,         // time scored (s)
                                    $answerCount,               // answer count
                                    $gradeTime,                 // grade time
                                    $answerText,                // answer
                                    $points,                    // score
                                    $totalMean,                 // total mean score
                                    $comment,                   // comment
                                    $studentEmail               // email
                                )
                            );
                        }
                    }
                }
            }
        }

        // finally print out students who never took the exam
        $em = $this->getDoctrine()->getManager();
        $results = $em->createQuery('
                SELECT s
                FROM BioStudentBundle:Student s
                INNER JOIN BioInfoBundle:Section e
                WITH s.section = e
                WHERE s NOT IN (
                    SELECT s1
                    FROM BioExamBundle:TestTaker t
                    JOIN BioStudentBundle:Student s1
                    WITH t.student = s1
                    AND t.exam = :exam
                )
                AND (e.name LIKE CONCAT(:section, '."'%'".') OR
                    :section IS NULL)
                ORDER BY s.lName ASC, s.fName ASC
            ')
            ->setParameter('section', $exam->getSection())
            ->setParameter('exam', $exam)
            ->getResult();

        foreach($results as $result) {
            $responseText[] = $this->echoArray(
                array(
                    '',
                    '',
                    '',
                    $result->getSid(),
                    '',
                    $result->getLName().', '.$result->getFName().($result->getMName()?' '.$result->getMName():''),
                    $result->getSection()->getName(),
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $result->getEmail()
                )
            );
        }

        $response = $this->render('BioPublicBundle:Template:blank.html.twig', array(
            'text' => implode("\n", $responseText)
            )
        );
        $response->headers->set(
            "Content-Type", 'application/plaintext'
            );

        $response->headers->set(
            'Content-Disposition', ('attachment; filename="'.$exam->getTitle().'.txt"')
            );
        return $response;
    }

    private function echoArray(array $args) {
        $text = "";
        for ($i = 0; $i < count($args) - 1; $i++) {
            $text.= $args[$i]."\t";
        }
        return $text.$args[count($args) - 1];
    }
}
