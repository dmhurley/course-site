<?php

namespace Bio\ExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

use Bio\DataBundle\Objects\Database;
use Bio\ExamBundle\Entity\Exam;
use Bio\ExamBundle\Entity\TestTaker;
use Bio\ExamBundle\Entity\Answer;
use Bio\ExamBundle\Entity\Grade;
use Bio\UserBundle\Entity\AbstractUserStudent;

use Bio\ExamBundle\Type\AnswerType;
use Bio\ExamBundle\Type\GradeType;

/**
 * @Route("/exam")
 */
class PublicController extends Controller {

	/**
	 * @Route("/review/{id}", name="review_exam")
	 * @Template()
	 */
	public function reviewAction(Request $request,Exam $exam) {
		$student = $this->get('security.context')->getToken()->getUser();

		$db = new Database($this, 'BioExamBundle:TestTaker');
		$taker = $db->findOne(array('student' => $student, 'exam' => $exam));

		if ($taker && $student) {
			return array(
				'taker' => $taker,
				'title' => $taker->getExam()->getTitle().' Review'
				);
		} else {
			return array(
				'exam' => $exam,
				'title' => $exam->getTitle().' Review'
			);
		}
	}

	/**
	 * @Route("/", name="exam_entrance")
	 * @Template()
	 */
	public function takeAction(Request $request) {
		$session = $request->getSession();
		$flash = $session->getFlashBag();

		$student = $this->get('security.context')->getToken()->getUser();

		$exam = null;
		$taker = null;

		$exams = $this->getNextExams($student->getSection()->getName());
		list($exam, $taker, $messages) = $this->findExam($exams, $student);

		if (!$taker || ($taker->getStatus() === 1 || $taker->getStatus() === 5)) {
			return $this->startAction($request, $exam, $taker, $messages, $student, $flash, $exams);
		} else if ($taker->getStatus() === 2) {
			return $this->examAction($request, $exam, $taker, $flash);
		} else if ($taker->getStatus() === 3) {
			return $this->waitAction($request, $exam, $taker, $flash);
		} else if ($taker->getStatus() === 4) {
			return $this->gradeAction($request, $exam, $taker, $flash);
		} else {
			$flash->set('failure', 'Error.');
			return $this->redirect($this->generateUrl('main_page'));
		}
	}
	private function getNextExams($section) {
		$em = $this->getDoctrine()->getManager();

		$queryString = '
			SELECT e
			FROM BioExamBundle:Exam e
			WHERE (e.gDate > :date
				OR (e.gDate = :date AND
					e.gEnd >= :time))
			AND (:section LIKE CONCAT(e.section, '."'%'".') OR 
				e.section IS NULL)
			ORDER BY e.tDate ASC, e.tStart ASC
		';

		return $em->createQuery($queryString)
			->setParameter('date', new \DateTime(), \Doctrine\DBAL\Types\Type::DATE)
			->setParameter('time', new \DateTime('-15 minutes'), \Doctrine\DBAL\Types\Type::TIME)
			->setParameter('section', $section)
			->getResult();
	}
	
	
	private function findExam(array $exams, AbstractUserStudent $student) {
		$db = new Database($this, 'BioExamBundle:TestTaker');

		// check to see if there are exams in the future
		if (count($exams) === 0) {
			return array(null, null, ["There are no more tests currently scheduled."]);
		}

		// get tests finished, tests to late to finish, tests now, tests in future
		// tests that are over are automatically not included
		$status = [];
		$currentTaker = null;

		foreach($exams as $exam) {
			$taker = $db->findOne(array('student' => $student, 'exam' => $exam));

			// has the person taken this test?
			if ($taker && $taker->getStatus() === 5) {
				$status[] = array('exam' => $exam, 'status' => 'finished');
			} else

			// test is over
			if (new \DateTime($exam->getGDate()->format('Y-m-d').' '.$exam->getGEnd()->format('H:i:s')) < new \DateTime('-1 minute')) {
				$status[] = array('exam' => $exam, 'status' => 'over');
			} else

			// are they too late to finish
			if ( (!$taker || $taker->getStatus() < 3) &&
				new \DateTime($exam->getTDate()->format('Y-m-d').' '.$exam->getTEnd()->format('H:i:s')) < new \DateTime('-1 minute')
				) {
				$status[] = array('exam' => $exam, 'status' => 'late'); 
			} else

			// the test is currently happening
			if (new \DateTime($exam->getTDate()->format('Y-m-d').' '.$exam->getTStart()->format('H:i:s')) <= new \DateTime()) {

				// have they started?
				if ($taker && !$currentTaker) {
					$currentTaker = $taker;

				// haven't started. create a test Taker
				} else if (!$currentTaker) {
					$currentTaker = new TestTaker();
					$currentTaker->setStudent($student)
						->setExam($exam);

					$db->add($currentTaker);
					$db->close();
				}
				$status[] = array('exam' => $exam, 'status' => 'current');
			} else

			// no tests are currently happening but there is one in the future
			if (!$currentTaker && new \DateTime($exam->getTDate()->format('Y-m-d').' '.$exam->getTStart()->format('H:i:s')) > new \DateTime()) {
				if ($taker) {
					$currentTaker = $taker;
				} else {
					$currentTaker = new TestTaker();
					$currentTaker->setStudent($student)
						->setExam($exam);

					$db->add($currentTaker);
					$db->close();
				}
				$status[] = array('exam' => $exam, 'status' => 'future');
			} else

			// test happens in the future
			if (new \DateTime($exam->getTDate()->format('Y-m-d').' '.$exam->getTStart()->format('H:i:s')) > new \DateTime()) {
				$status[] = array('exam' => $exam, 'status' => 'future');
			}
		}

		// generate message
		$messages = [];
		foreach($status as $entry) {
			$status = $entry['status'];
			$exam = $entry['exam'];

			if ($status === 'finished') {
				$messages[] = "You have already finished ".$exam->getTitle().".";
			} else if ($status === 'over') {
				$messages[] = $exam->getTitle().' is over.';
			} else if ($status === 'late') {
				$messages[] = "It is too late to submit and grade ".$exam->getTitle().".";
			} else if ($status === 'current') {
				$messages[] = $exam->getTitle()." is happening now.";
			} else if ($status === 'future') {
				$messages[] = $exam->getTitle()." will open on ".$exam->getTDate()->format('m/d Y')." at ".$exam->getTStart()->format('h:i a').".";
				break;
			}
		}

		if ($currentTaker) {
			return array($currentTaker->getExam(), $currentTaker, $messages);
		} else {
			return array(null, null, $messages);
		}
	}

	private function startAction(Request $request, Exam $exam, TestTaker $taker, array $messages = array(), AbstractUserStudent $student, FlashBag $flash, array $exams) {
		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		if ($request->getMethod() === "POST") {
			if ($taker && $exam && new \DateTime( $exam->getTDate()->format('Y-m-d').' '.$exam->getTStart()->format('H:i:s') ) <= new \DateTime()) {
				$taker->setStatus(2);
				$taker->setTimestamp([
						'name' => 'started',
						'time' => new \DateTime()
					]);
				foreach($exam->getQuestions() as $question) {
					$answer = new Answer();
					$answer->setQuestion($question)
						->setTestTaker($taker)
						->setAnswer("");
					$taker->addAnswer($answer);
					$this->getDoctrine()->getManager()->persist($answer);
				}
				$this->getDoctrine()->getManager()->flush();
				$flash->set('success', 'Exam started.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			} else {
				// $flash->set('failure', $message);
			}
		}

		$global = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array());

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('
				SELECT
					e.id as id,
					e.title as title,
					e.tDate as tdate,
					e.tStart as tstart,
					e.tEnd as tend,
					e.gDate as gdate,
					e.gStart as gstart,
					e.gEnd as gend,
					t as taker,
					t.status as status
				FROM BioExamBundle:Exam e
				LEFT JOIN BioExamBundle:TestTaker t
				WITH (
					t.exam = e
					AND (
						t.student = :student
						OR
						t.id IS NULL
					)
				)
				WHERE :section LIKE CONCAT(e.section, '."'%'".')
				OR e.section IS NULL
				ORDER BY e.tDate ASC, e.tStart ASC
			')
			->setParameter('student', $student)
			->setParameter('section', $student->getSection()->getName());

		return $this->render('BioExamBundle:Public:start.html.twig', array(
				'form' => $form->createView(),
				'global' => $global,
				'exam' => $exam,
				'messages' => $messages,
				'exams' => $query->getResult(),
				'title' => 'Begin Test'
			));
	}

	private function examAction(Request $request, Exam $exam, TestTaker $taker, FlashBag $flash) {
		$form = $this->createFormBuilder($taker)
				->add('answers', 'collection', array(
						'type' => new AnswerType(),
					)
				)
				->add('save', 'submit')
				->add('submit', 'submit')
				->getForm();

		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			$flash->set('success', 'Answers saved.');
			$this->getDoctrine()->getManager()->flush();

			if ($form->isValid() && $form->get('submit')->isClicked()) {
				$taker->setStatus(3)
					->setTimestamp([
							'name' => 'submitted',
							'time' => new \DateTime()
						]);
				$this->getDoctrine()->getManager()->flush();
				$flash->set('success', 'Answers submitted.');

				return $this->redirect($this->generateUrl('exam_entrance'));
			} else if (!$form->get('save')->isClicked()) {
				$flash->set('failure', 'Invalid answer(s).');
			}
		}
		return $this->render('BioExamBundle:Public:exam.html.twig', array(
				'form' => $form->createView(),
				'taker' => $taker,
				'title' => $exam->getTitle()
			));
	}

	private function waitAction(Request $request, Exam $exam, TestTaker $taker, FlashBag $flash) {
		$global = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array());

		if ($taker->getGradedNum() >= $global->getGrade() && count($taker->getAssigned()) === 0) {
			$code = base64_encode(
						$exam->getId().':'.
						$taker->getId().':'.
						$taker->getStudent()->getSid()
					);
			$taker->setStatus(5)
				->setTimestamp([
						'name' => 'finished',
						'time' => new \DateTime(),
						'code' => $code
					]);

			$this->getDoctrine()->getManager()->flush();

			$flash->set('success', 'Finished exam. Confirmation code: 
				<span style="cursor:text;-webkit-user-select:initial;user-select:initial;">'.$code.'</span>');
			$flash->set('banner_stay', true);

			if ($taker->getStudent()->getEmail() !== '') {
				$info = (new Database($this, 'BioInfoBundle:Info'))->findOne(array());

				$message = \Swift_Message::newInstance()
					->setSubject($taker->getExam()->getTitle().' confirmation')
					->setFrom($info->getEmail())
					->setTo($taker->getStudent()->getEmail())
					->setBody($this->renderView('BioExamBundle:Public:email.html.twig',
						array('code' => $code, 'taker' => $taker)
						)
					)
					->setContentType('text/html');
				$this->get('mailer')->send($message);
			}
			return $this->redirect($this->generateUrl('exam_entrance'));


		} else if (new \DateTime($exam->getGDate()->format('Y-m-d').$exam->getGStart()->format('H:i:s')) >
			new \DateTime(/**/)) {
			$flash->set('failure', 
					'Grading starts at '.
					$exam->getGdate()->format('m/d'). ' at '.
					$exam->getGStart()->format('h:i a')
				);
		} else {

			if ($request->getMethod() === "POST") {
				if (count($taker->getAssigned()) <= 0) {
					$this->forward('BioExamBundle:Public:check', array('id' => $taker->getId()));
				}

				if (count($taker->getAssigned()) > 0) {
					$this->handleWaitPost($request, $taker);
				}

				return $this->redirect($this->generateUrl('exam_entrance'));
			}

		}

		return $this->render('BioExamBundle:Public:wait.html.twig', array(
				'taker' => $taker,
				'exam' => $exam,
				'global' => $global,
				'title' => 'Finding tests to grade..'
			));
	}

	private function handleWaitPost(Request $request, TestTaker $taker) {
		$assigned = $taker->getAssigned()->toArray();
		reset($assigned);
		$taker->setStatus(4)
			->setTimestamp([
					'name' => 'grading',
					'time' => new \DateTime(),
					'who' => current($assigned)->getStudent()->getUsername()
				]);
		$this->getDoctrine()->getManager()->flush();

	}

	private function gradeAction(Request $request, Exam $exam, TestTaker $taker, FlashBag $flash) {
		$assigned = $taker->getAssigned()->toArray();
		reset($assigned);
		$target = current($assigned);

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('
				SELECT g
				FROM BioExamBundle:Grade g
				INNER JOIN BioExamBundle:Answer a
				WITH g.answer = a
				WHERE a.testTaker = :target
				AND g.grader = :taker
			')
			->setParameter('target', $target)
			->setParameter('taker', $taker);

		$grades = ['grades' => $query->getResult()];

		$comments = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array())->getComments();
		$form = $this->createFormBuilder($grades)
			->add('grades', 'collection', array(
					'type' => new GradeType($comments)
				)
			)
			->add('submit', 'submit')
			->getForm();

		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$taker->graded($target)
					->setStatus(3)
					->setTimestamp([
							'name' => 'graded',
							'time' => new \DateTime(),
							'who' => $target->getStudent()->getUsername()
						]);
				$em->flush();
				$flash->set('success', 'Test graded.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			} else {
				$flash->set('failure', 'Invalid form.');
			}
		}

		return $this->render('BioExamBundle:Public:grade.html.twig', array(
				'form' => $form->createView(),
				'taker' => $taker,
				'start' => $taker->getTimestamp('grading')[0]['time'],
				'title' => 'Grade Exam'
			)
		);
	}

	/**
	 * @Route("/{id}/check.json", name="check")
	 * @Template("BioExamBundle:Public:check.json.twig")
	 */
	public function checkAction(Request $request, TestTaker $you) {
		if ($you->getStudent() !== $this->get('security.context')->getToken()->getUser()) {
			throw $this->createNotFoundException();
		}

		$global = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array());
		$haveGraded = array_merge($you->getAssigned()->toArray(), $you->getGraded()->toArray());
		if ($you->getGradedNum() >= $global->getGrade() || count($you->getAssigned()) > 0 ) {
			return array('success' => true);
		}

		$force = $request->query->has('please');

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('
				SELECT t
				FROM BioExamBundle:TestTaker t
				WHERE t.exam = :exam
				AND t.id <> :id
				AND t.status >= 3
				AND t.gradedByNum < :max
			')
			->setParameter('exam', $you->getExam())
			->setParameter('id', $you->getId())
			->setParameter('max', $force?99999:$global->getGrade())
		;

		$results = $query->getResult();
		if (count($results) > 0) {
			$target = $results[rand(0, count($results) - 1)];
			if (in_array($target, $haveGraded)) {
				return array('success' => false, 'message' => 'Duplicate found. Trying again.');
			}
			$target->addIsGrading($you);
			$em->flush(); // save changes riiight away
			$you->addAssigned($target)
				->setTimestamp([
						'name' => 'matched',
						'time' => new \DateTime(),
						'who' => $target->getStudent()->getUsername()
					]);

			foreach($target->getAnswers() as $answer) {
				$grade = new Grade();
				$grade->setPoints(null)
					->setComment("")
					->setGrader($you)
					->setAnswer($answer);
				$em->persist($grade);
			}
			$em->flush();
			return array('success' => true);
		} else {
			return array('success' => false, 'message' => 'No tests available.');
		}
	}
}