<?php

namespace Bio\ExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\ExamBundle\Entity\Exam;
use Bio\ExamBundle\Entity\TestTaker;
use Bio\ExamBundle\Entity\Answer;
use Symfony\Component\Validator\Constraints as Assert;
use Bio\ExamBundle\Entity\Grade;

use Bio\ExamBundle\Type\AnswerType;
use Bio\ExamBundle\Type\GradeType;

/**
 * @Route("/exam")
 */
class ExamController extends Controller {

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
			$request->getSession()->getFlashBag()->set('failure', 'Could not find entry.');
		}
	}

	/**
	 * @Route("/take", name="exam_take")
	 * @Template()
	 */
	public function takeAction(Request $request) {
		$session = $request->getSession();
		$flash = $session->getFlashBag();

		$student = $this->get('security.context')->getToken()->getUser();

		$exam = null;
		$taker = null;

		$exams = $this->getNextExams($student->getSection()->getName());
		list($exam, $taker, $message) = $this->findExam($exams, $student);

		if (!$taker || ($taker->getStatus() === 1 || $taker->getStatus() === 6)) {
			return $this->startAction($request, $exam, $taker, $message, $student, $flash, $exams);
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
			->setParameter('time', new \DateTime(), \Doctrine\DBAL\Types\Type::TIME)
			->setParameter('section', $section)
			->getResult();
	}
	private function findExam($exams, $student) {
		$db = new Database($this, 'BioExamBundle:TestTaker');
		$message = null;
		foreach($exams as $exam) {
			$taker = $db->findOne(array('student' => $student, 'exam' => $exam));
			if (!$taker) {					// if student hasn't started, create taker
				$taker = new TestTaker();
				$taker->setStudent($student)
					->setExam($exam);
				$db->add($taker);
				$db->close();

				return array($exam, $taker, null);
			} else {						// if student has started
				if ($taker->getStatus() === 6) {
					continue;
					$message = 'You have already finished '.$exam->getTitle().'.';
				} else if (
					$taker->getStatus() < 4 && 
					new \DateTime(
							$exam->getTDate()->format('Y-m-d').
							$exam->getTEnd()->format('H:i:s')
						) < new \DateTime()
				) {
					$message = "It is too late to take ".$e->getTitle().".";
				} else if ( 
					$taker->getStatus() < 6 &&
					new \DateTime(
							$exam->getGDate()->format('Y-m-d').
							$exam->getGEnd()->format('H:i:s')
						) < new \DateTime()
				) {
					$message = "It is too late to grade ".$e->getTitle().".";
				} else {
					return array($exam, $taker, $message);
				}
			}
		}
		return array(null, null, 'No more tests currently scheduled.');
	}

	private function startAction(Request $request, $exam, $taker, $message, $student, $flash, $exams) {
		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		if ($request->getMethod() === "POST") {
			if ($taker && $exam->getTStart() <= new \DateTime()) {
				$taker->setStatus(2);
				$taker->setTimestamp('started', new \DateTime());
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
				return $this->redirect($this->generateUrl('exam_take'));
			}
			$flash->set('failure', 'Exam has not started yet.');
		}

		$global = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array());
		$takers = (new Database($this, 'BioExamBundle:TestTaker'))->find(array('student' => $student), array(), false);
		return $this->render('BioExamBundle:Exam:start.html.twig', array(
				'form' => $form->createView(),
				'global' => $global,
				'exam' => $exam,
				'message' => $message,
				'takers' => $takers,
				'exams' => $exams,
				'title' => 'Begin Test'
			));
	}

	private function examAction(Request $request, $exam, $taker, $flash) {
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
					->setTimestamp('submitted', new \DateTime());
				$this->getDoctrine()->getManager()->flush();
				$flash->set('success', 'Answers submitted.');

				return $this->redirect($this->generateUrl('exam_take'));
			} else if (!$form->get('save')->isClicked()) {
				$flash->set('failure', 'Invalid answer(s).');
			}
		}
		return $this->render('BioExamBundle:Exam:exam.html.twig', array(
				'form' => $form->createView(),
				'taker' => $taker,
				'title' => $exam->getTitle()
			));
	}

	private function waitAction(Request $request, $exam, $taker, $flash) {
		$global = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array());

		if ($taker->getGradedNum() >= $global->getGrade() && count($taker->getAssigned() === 0)) {
			$code = base64_encode(
						$exam->getId().':'.
						$taker->getId().':'.
						$taker->getStudent()->getSid()
					);
			$taker->setStatus(6)
				->setTimestamp('finished', new \DateTime())
				->setTimestamp('code', $code);

			$this->getDoctrine()->getManager()->flush();

			$flash->set('success', 'Finished exam. Confirmation code: '.$code);
			$flash->set('banner_stay', true);

			if ($taker->getStudent()->getEmail() !== '') {
				$info = (new Database($this, 'BioInfoBundle:Info'))->findOne(array());

				$message = \Swift_Message::newInstance()
					->setSubject($taker->getExam()->getTitle().' confirmation')
					->setFrom($info->getEmail())
					->setTo($taker->getStudent()->getEmail())
					->setBody($this->renderView('BioExamBundle:Exam:email.html.twig',
						array('code' => $code, 'taker' => $taker)
						)
					)
					->setContentType('text/html');
				$this->get('mailer')->send($message);
			}
			return $this->redirect($this->generateUrl('exam_take'));


		} else if (new \DateTime($exam->getGDate()->format('Y-m-d').$exam->getGStart()->format('H:i:s')) >
			new \DateTime(/**/)) {
			$flash->set('failure', 
					'Grading starts at '.
					$exam->getGStart()->format('m/d'). ' at '.
					$exam->getGDate()->format('h:i a')
				);
		}

		return $this->render('BioExamBundle:Exam:wait.html.twig', array(
				'taker' => $taker,
				'exam' => $exam,
				'global' => $global,
				'title' => 'Finding tests to grade..'
			));
	}

	private function gradeAction(Request $request, $exam, $taker, $flash) {
		$assigned = $taker->getAssigned()->toArray();
		reset($assigned);
		$target = current($assigned);

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('
				SELECT g
				FROM BioExamBundle:Grade g
				INNER JOIN BioExamBundle:Answer a
				WITH g.answer = a
				WHERE a.testTaker = :taker
			')
			->setParameter('taker', $target);

		$grades = ['grades' => $query->getResult()];


		$form = $this->createFormBuilder($grades)
			->add('grades', 'collection', array(
					'type' => new GradeType()
				)
			)
			->add('submit', 'submit')
			->getForm();

		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$taker->graded($target)
					->setStatus(3)
					->setTimestamp('graded_'+$target->getStudent()->getUsername(), new \DateTime());
				$em->flush();
				$flash->set('success', 'Test graded.');
				return $this->redirect($this->generateUrl('exam_take'));
			} else {
				$flash->set('failure', 'Invalid form.');
			}
		}

		return $this->render('BioExamBundle:Exam:grade.html.twig', array(
				'form' => $form->createView(),
				'taker' => $taker,
				'start' => $taker->getTimestamp('submitted'),
				'title' => 'Grade Exam'
			)
		);
	}

	/**
	 * @Route("/check.json", name="check")
	 * @Template("BioExamBundle:Exam:check.json.twig")
	 */
	public function checkAction(Request $request) {
		$you = (new Database($this, 'BioExamBundle:TestTaker'))->findOne(array('id' => $request->request->get('a')));
		$global = (new Database($this, 'BioExamBundle:ExamGlobal'))->findOne(array());
		$haveGraded = array_merge($you->getAssigned()->toArray(), $you->getGraded()->toArray());
		if ($you->getGradedNum() >= $global->getGrade()) {
			return array('success' => true);
		}

		$force = $request->request->has('please');

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
				->setStatus(4)
				->setTimestamp('matched', new \DateTime());

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