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
use Bio\ExamBundle\Entity\TestTaker;
use Bio\ExamBundle\Entity\Answer;
use Bio\ExamBundle\Entity\Grade;

/**
 * @Route("/exam")
 */
class PublicController extends Controller
{	
	/**
	 * @Route("/review/{id}", name="review_exam")
	 * @Template()
	 */
	public function reviewAction(Request $request,Exam $exam) {
		$student = $this->get('security.context')->getToken()->getUser();

		$db = new Database($this, 'BioExamBundle:TestTaker');
		$taker = $db->findOne(array('student' => $student, 'exam' => $exam));

		if ($taker && $student) {
			return array('taker' => $taker);
		} else {
			$request->getSession()->getFlashBag()->set('failure', 'Could not find entry.');
		}
	}

	/**
	 * @route("", name="exam_entrance")
	 */
	public function indexAction(Request $request) {
		$session = $request->getSession();
		$flash = $session->getFlashBag();

		$a = $this->get('security.context');
       	$student = $a->getToken()->getUser();

		$exam = null;
		$taker = null;
		$message = null;
		$db = new Database($this, 'BioExamBundle:TestTaker');


		// get all exams where the grading period hasn't ended.
			$exams = $this->getNextExam($student);
			foreach($exams as $e) {
				$t = $db->findOne(array('student' => $student, 'exam' => $exam));
				if ( (!$t || $t->getStatus() < 4) && new \DateTime($e->getTDate()->format('Y-m-d ').$e->getTEnd()->format('H:i:s')) < new \DateTime()) {
					// not started/or submitted yet
					// too late to start/finish exam
					$message = "It is too late to take ".$e->getTitle().".";
				} else if ( (!$t || $t->getStatus() < 6) && new \DateTime($e->getGDate()->format('Y-m-d ').$e->getGEnd()->format('H:i:s')) < new \DateTime()) {
					// not finished grading / or not started
					// to late to start/finish grading

					$message = "It is too late to grade ".$e->getTitle().".";
					// do nothing? //impossible to get to???
				} else if (!$t) {
					// never took this exam
					// fine to start?
					$exam = $e;
					break;
				} else if ($t->getStatus() === 6) {
					// finished
					// find next exam
				}
			}

		if ($exam) {
			if (new \DateTime($e->getTDate()->format('Y-m-d ').$e->getTStart()->format('H:i:s')) < new \DateTime()) {
				// get the appropriate test taker for the student/exam combo
				$taker = $db->findOne(array('student' => $student, 'exam' => $exam));

				if (!$taker) {
					$taker = new TestTaker();
					$taker->setStatus(1)
						->setStudent($student)
						->setExam($exam);

					$db->add($taker);	
					$db->close();
				}
			}
		}

		if (!$taker || $taker->getStatus() === 1 || $taker->getStatus() === 6) {
			return $this->startAction($request, $exam, $taker, $student, $message, $db, $exams);
		} else {
			if ($taker->getStatus() === 2) {
				return $this->examAction($request, $exam, $taker, $db);
			}

			// skips status code 3

			if ($taker->getStatus() === 4) {
				
				return $this->waitAction($request, $exam, $taker, $db);
			}

			if ($taker->getStatus() === 5) {
				return $this->gradeAction($request, $exam, $taker, $db);
			}
		}
	}

	/**
	 * Counts down to test start, then makes start button available
	 * 
	 * status 1
	 */
	private function startAction(Request $request, $exam, $taker, $student, $message, $db, $exams) {
		// create form
		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		// if they pressed submit
		if ($request->getMethod() === "POST") {
			if (!$taker || $taker->getStatus() !== 1) {
				return $this->redirect($this->generateUrl('exam_entrance'));
			}

			// if the exam has started
			if ($exam->getTStart() <= new \DateTime()) {
				$taker->setStatus(2);

				foreach($exam->getQuestions() as $question) {
					$answer = new Answer();
					$answer->setQuestion($question)	
						->setTestTaker($taker)
						->setAnswer("");
					$taker->addAnswer($answer);
					$db->add($answer);
				}

				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Exam started.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			} else {
				// INSPECT ELEMENT CHEATERS GO HERE!
				$request->getSession()->getFlashBag()->set('failure', 'Exam has not started yet.');
			}
		}
		$db = new Database($this, 'BioExamBundle:ExamGlobal');
		$global = $db->findOne(array());
		$db = new Database($this, 'BioExamBundle:TestTaker');
		$takers = $db->find(array('student' => $student), array(), false);
		return $this->render('BioExamBundle:Public:start.html.twig', array('form' => $form->createView(), 'global'=>$global, 'exam' => $exam, 'message' => $message, 'takers' => $takers, 'exams' => $exams, 'title' => 'Begin Test'));
	}

	/**
	 * Allows user to take exam and submit answers
	 * status 2
	 *
	 * @Template()
	 */
	private function examAction(Request $request, $exam, $taker, $db) {
		// if they submitted the exam
		if ($request->getMethod() === "POST") {

			$areErrors = false;
			$validator = $this->get('validator');

			if (count($request->request->keys()) !== count($exam->getQuestions()) + 1){
				$request->getSession()->getFlashBag()->set('failure', 'Error.');
			} else {

				foreach($request->request->keys() as $key) {
					if ($key === 'save' || $key === 'submit') {
						continue;
					}
					$answer = $this->findObjectByFieldValue($key, $taker->getAnswers(), 'id');
					if ($answer) {
						$answer->setAnswer($request->request->get($key));
					} else {
						$request->getSession()->getFlashBag()->set('failure', 'Invalid IDs.');
						$areErrors = true;
						break;
					}

					$errors = $validator->validate($answer);
					if (count($errors) > 0 && $request->request->has('submit')) {
						$request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
						$areErrors = true;
						$answer->errors = $errors;
					}
				}

				try {
					if (!$areErrors && $request->request->has('submit')) {
						$taker->setStatus(4);
						$db->close();
						$request->getSession()->getFlashBag()->set('success', 'Answers submitted.');
						return $this->redirect($this->generateUrl('exam_entrance'));
					} else {
						if ($request->request->has('save')) {
							$request->getSession()->getFlashBag()->set('success', 'Answers saved.');
						}
						$db->close();
					}
				} catch (BioException $e) {
					$request->getSession()->getFlashBag()->set('failure', 'Could not save progress.');
				}
			}
		}
		return $this->render('BioExamBundle:Public:exam.html.twig', array('taker' => $taker, 'title' => $exam->getTitle()));
	}

	/**
	 * User waits here until they have someone to grade
	 *
	 * status 4
	 */
	private function waitAction(Request $request, $exam, $taker, $db) {
		$db = new Database($this, 'BioExamBundle:ExamGlobal');
		$global = $db->findOne(array());
		if ($taker->getNumGraded() >= $global->getGrade()) {
			$code = $exam->getId().':'.$taker->getId().':'.$taker->getStudent()->getSid();
			$code = base64_encode($code);
			$taker->setStatus(6);
			$db->close();
			$request->getSession()->getFlashBag()->set('success', "Finished exam.");


			// email confirmation code
			$db = new Database($this, 'BioInfoBundle:Info');
            $info = $db->findOne(array());
			$message = \Swift_Message::newInstance()
				->setSubject($taker->getExam()->getTitle().' confirmation')
				->setFrom($info->getEmail())
				->setTo($taker->getStudent()->getEmail())
				->setBody($this->renderView('BioExamBundle:Public:email.html.twig', array('code' => $code, 'taker' => $taker)))
				->setContentType('text/html');
			$this->get('mailer')->send($message);


			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		$now = new \DateTime();
		$gradeStart = new \DateTime($exam->getGDate()->format("Y-m-d")." ".$exam->getGStart()->format("H:i:s"));
		if ($now < $gradeStart) {
			$request->getSession()->getFlashBag()->set('failure', 'Grading starts at '.$gradeStart->format('m/d').' at '. $gradeStart->format('h:i a').'.');
		} else

		// if the pressed submit
		if ($request->getMethod() === "POST") {

			$target = null;
			try {
				$target = $this->match($taker);
			} catch (BioException $e) {
			}

			// if there actually was a match
			if ($target !== null) {
				$grade = new Grade();

				$taker->setStatus(5)
					->setGrading($target);
				$target->addGradedBy($taker);

				foreach($target->getAnswers() as $answer) {
					$grade = new Grade();
					$grade->setGrader($taker)
						->setAnswer($answer);
					$answer->addPoint($grade);
					$db->add($grade);
				}

				$db->close();

				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioExamBundle:Public:wait.html.twig', array('taker' => $taker, 'exam' => $exam, 'title' => 'Finding Test'));
	}

	/**
	 * User grades another users' test, status is incremented up/down depending on tests graded
	 *
	 * status 5
	 */
	private function gradeAction(Request $request, $exam, $taker, $db) {
		if ($request->getMethod() === "POST") {
			if (count($request->request->keys()) !== count($exam->getQuestions())) {
				$request->getSession()->getFlashBag()->set('failure', 'Answer all the questions.');
			} else {
				$targetAnswers = $taker->getGrading()->getAnswers();
				foreach($request->request->keys() as $key) { // keys are answer ids
					$answer = $this->findObjectByFieldValue($key, $targetAnswers, 'id');
					if ($answer && $answer->getQuestion()->getPoints() >= (int)$request->request->get($key) && (int)$request->request->get($key) >= 0){
						$answer->grade($taker, (int) $request->request->get($key));
					} else {
						$request->getSession()->getFlashBag()->set('failure', "Invalid form.");
						return $this->render('BioExamBundle:Public:grade.html.twig', array('taker' => $taker->getGrading(), 'start' => $taker->getTimecard()[5], 'title' => 'Grade Exam'));
					}
				}
				$taker->addGraded($taker->getGrading())
					->setGrading(null);

				$db = new Database($this, 'BioExamBundle:ExamGlobal');
				$global = $db->findOne(array());

				$request->getSession()->getFlashBag()->set('success', 'Test graded. '.($global->getGrade()-count($taker->getGraded()).' left.'));
				$taker->setStatus(4);

				$db->close();
				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioExamBundle:Public:grade.html.twig', array('taker' => $taker->getGrading(), 'start' => $taker->getTimecard()[5], 'title' => 'Grade Exam'));
	}

	/**
	 * Recieves a post request containing student_id, exam_id. Attempts to find someone for user to grade
	 *
	 * @Route("/check.json", name="check")
	 * @Template("BioExamBundle:Public:check.json.twig")
	 */
	public function checkAction(Request $request) {
		if ($request->request->has('a')) {
			$id = $request->request->get('a');

			$db = new Database($this, 'BioExamBundle:TestTaker');
			$you = $db->findOne(array('id' => $id));

			if (!$you) {
				return array('success' => false, 'message' => 'Invalid Id.');
			}

			try {
				return array('success' => true, 'message' => $this->match($you)->getStudent()->getSid());
			} catch (BioException $e) {
				return array('success' => false, 'message' => $e->getMessage());
			}
		}
		return array('success' => false, 'message' => 'Invalid post request.');
	}

	private function match($you) {
		if ($you->getGrading() ) {
			return $you->getGrading();
		}

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('
				SELECT t, COUNT(g) as c
				FROM BioExamBundle:TestTaker t 
				LEFT JOIN t.gradedBy g 
				WHERE t.exam = :exam
				AND t.id <> :id
				AND t.status >= 4
				GROUP BY t.id
				ORDER BY c
			');

		$query->setParameter('exam', $you->getExam());
		$query->setParameter('id', $you->getId());

		$targets = $query->getResult();

		if (count($targets) === 0) {
			throw new BioException("No other exams.");
		}

		$target = null;
		for ($i = 0; $i < count($targets); $i++) {
			if (!$you->getGraded()->contains($targets[$i][0]) ) {
				$target = $targets[$i][0];
				break;
			}
		}

		if ($target === null) {
			throw new BioException("No other tests.");
		}
		return $target;
	}

	private function getNextExam($student) {
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()->select('p')
			->from('BioExamBundle:Exam', 'p')
			->where('p.gDate > :date
					 OR (p.gDate = :date 
					 	 AND p.gEnd >= :time)'
				)
			->andWhere(":section LIKE CONCAT(p.section, '%')")
			->andWhere(':student NOT IN (SELECT s FROM BioExamBundle:TestTaker t JOIN t.student s WHERE t.status = 6 AND t.exam = p)')
			->addOrderBy('p.tDate', 'ASC')
			->addOrderBy('p.tStart', 'ASC')
			->setParameter('date', new \DateTime(), \Doctrine\DBAL\Types\Type::DATE)
			->setParameter('time', new \DateTime(), \Doctrine\DBAL\Types\Type::TIME)
			->setParameter('section', $student->getSection()->getName())
			->setParameter('student', $student)
			->getQuery();
		$result = $query->getResult();

		return $result;
	}

	private function findObjectByFieldValue($needle, $haystack, $field) {
		$getter = 'get'.ucFirst($field);

		foreach ($haystack as $straw) {
			if (call_user_func_array(array($straw, $getter), array()) === $needle) {
				return $straw;
			} 
		}
		return null;
	}
}
