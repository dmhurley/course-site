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

/**
 * @Route("/exam")
 */
class PublicController extends Controller
{

	/**
	 * @Route("/", name="exam_entrance")
	 */
	public function gateAction(Request $request) {
		$session = $request->getSession();
		$flash = $session->getFlashBag();
		try {
			$exam = $this->getNextExam(); // try
		} catch (BioException $e) {
			return $this->signAction($request, $exam);
		}

		if ($request->query->has('logout')) {
			$session->invalidate();
		}

		if ($session->has('sid') && $session->has('exam')){
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$taker = $db->findOne(array('sid' => $session->get('sid'), 'exam' => $session->get('exam')));

			if ($taker) {
				// route to page based on status
				if ($taker->getStatus() === 1) {

					$flash->set('success', 'Signed in.');
					return $this->startAction($request, $exam, $taker, $db);
				} else if ($taker->getStatus() === 2) {

					if (!$flash->has('success'))
						$flash->set('success', 'You previously started the test.');

					return $this->examAction($request, $exam, $taker, $db);
				} else if ($taker->getStatus() === 3) {

					if (!$flash->has('success'))
						$flash->set('success', 'Review your answers and submit.');

					$request->getSession()->getFlashBag()->set('success', 'Review your answers and submit.');
					return $this->reviewAction($request, $exam, $taker, $db);
				} else if ($taker->getStatus() == 4) {

					if (!$flash->has('success'))
						$flash->set('success', 'You have already finished your exam. Please wait to grade a test.');

					return $this->confirmationAction($request, $exam, $taker, $db);
				} else if ($taker->getStatus() == 5) {

					if (!$flash->has('success'))
						$flash->set('success', 'You still need to grade this test.');

					return $this->gradeAction($request, $exam, $taker, $db);
				} else if ($taker->getStatus() == 6) {

					if (!$flash->has('success'))
						$flash->set('success', 'You have already completed the exam.');
				}
			} else {
				$session->getFlashBag()->set('failure', 'Not signed in.');
			}
		}
		
		return $this->signAction($request, $exam);
	}
	
	private function signAction(Request $request, $exam) {
		// create signin form
		$form = $this->createFormBuilder()
			->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false, 'attr' => array('pattern' => '[0-9]{7}', 'title' => 'Seven digit student ID.'))) // add a pattern to this TODO
			->add('lName', 'text', array('label' => 'Last Name:', 'mapped' => false))
			->add('sign in', 'submit')
			->getForm();

		// check to see if they submitted login stuff
		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);

			// check to see if they submitted ALL the login stuff
			// cant' use form validate because they aren't mapped
			if ($form->has('sid') && $form->has('lName')) {

				// check to see if student exists
				$db = new Database($this, 'BioStudentBundle:Student');
				try {
					$db->find(array('sid' => $form->get('sid')->getData(), 'lName' => $form->get('lName')->getData()));
				} catch (BioException $e) {
					$request->getSession()->getFlashBag()->set('failure', "Could not find anyone with that student ID and last name.");
					return $this->render('BioExamBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Log In'));
				}

				// check to see if they've already logged in
				// add to database if they haven't
				$db = new Database($this, 'BioExamBundle:TestTaker');
				$taker = $db->findOne(array('sid' => $form->get('sid')->getData(), 'exam' => $exam->getId()));
				if (!$taker) {
					$taker = new TestTaker();
					$taker->setSid($form->get('sid')->getData())
						->setExam($exam->getId())
						->setStatus(1);
					$db->add($taker);
					$db->close();
				}

				// overwrite session in case multiple people go in succession
				$session = $request->getSession();
				$session->set('sid', $taker->getSid());
				$session->set('exam', $exam->getId());

				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioExamBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Log In'));
	}

	
	private function startAction(Request $request, $exam, $taker, $db) {
		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		// check to see if they pressed the start button
		if ($request->getMethod() === "POST") {

			// check to see if they started on team
			if ($exam->getStart() <= new \DateTime()) {
				$taker->setStatus(2);
				$taker->setVar('started', new \DateTime());
				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Exam started.');
				return $this->redirect($this->generateUrl('exam_entrance'));

			} else {

				// if they got to this early
				$request->getSession()->getFlashBag()->set('failure', "Exam has not started yet.");
			}
		}

		return $this->render('BioExamBundle:Public:start.html.twig', array('form' => $form->createView(), 'exam' => $exam, 'title' => 'Begin Test'));
	}

	private function examAction(Request $request, $exam, $taker, $db) {
		// if they pressed submit
		if ($request->getMethod() === "POST") {
			$answers = array();

			// add their answers to array by key value IF the keys match the exam
			foreach($request->request->keys() as $key) {
				if ($this->arrayContainsId($exam->getQuestions()->toArray(), $key)){
					$answers[$key] = $request->request->get($key);
				} else {
					$request->getSession()->getFlashBag()->set('failure', 'Error');
					return $this->render('BioExamBundle:Public:exam.html.twig', array('exam' => $exam, 'taker' => $taker, 'title' => $exam->getName()));
				}
			}

			// save stuff
			$taker->setVar('answers', $answers);
			$taker->setStatus(3);
			$db->close();

			$request->getSession()->getFlashBag()->set('success', 'Answers saved. Please review your answers.');
			return $this->redirect($this->generateUrl('exam_entrance'));
		}
		return $this->render('BioExamBundle:Public:exam.html.twig', array('exam' => $exam, 'taker' => $taker, 'title' => $exam->getName()));
	}

	private function reviewAction(Request $request, $exam, $taker, $db) {
		$form = $this->createFormBuilder()
			->add('edit', 'submit')
			->add('submit', 'submit')
			->getForm();

		// if they pressed a button
		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);

			// if they want to edit stuff
			if ($form->get('edit')->isClicked()) {
				$taker->setStatus(2);
				$taker->setVar('edited', true); // save that they edited it
				$db->close();
				return $this->redirect($this->generateUrl('exam_entrance'));

			// if they want to submit it
			} else {
				$taker->setStatus(4);
				$taker->setVar('ended', new \DateTime());

				// check to see if they went over the time limit, let them continue working. Just marks it.
				$diff = date_diff($taker->getVar('ended'), $taker->getVar('started'));
				$duration = new \DateInterval('PT'.$exam->getDuration()."M");
				if ($diff->format('%Y-%M-%D %H:%I:%S') > $duration->format('%Y-%M-%D %H:%I:%S') ){
					$request->getSession()->getFlashBag()->set('success', 'Went over time limit. Answers saved.');
					$taker->setVar('error', -1);
				} else {
					$request->getSession()->getFlashBag()->set('success', 'Answers saved.');
				}
				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Exam submitted.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioExamBundle:Public:review.html.twig', array('form'=>$form->createView(), 'exam' => $exam, 'taker' => $taker, 'title' => 'Review Answers.'));
	}

	private function confirmationAction(Request $request, $exam, $taker, $db) {
		// if they pressed the grade button
		if ($request->getMethod() === "POST") {
			// if they haven't cheated set them up and forward them
			if ($taker->getGrader() !== '') {
				$taker->setStatus(5);
				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Grade this test.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioExamBundle:Public:confirmation.html.twig', array('taker' => $taker, 'exam' => $exam, 'title' => 'Exam Submitted'));
	}

	private function gradeAction(Request $request, $exam, $taker, $db) {
		// finds the person they're grading
		$db = new Database($this, 'BioExamBundle:TestTaker');
		$target = $db->findOne(array('sid' => $taker->getGrader()));

		// if they pressed submit
		if ($request->getMethod() === "POST") {

			// get the points they assigned from the form
			$points = array();

			// make sure they graded everything
			if (count($request->request->keys()) !== count($exam->getQuestions())) {
				$request->getSession()->getFlashBag()->set('failure', 'You did not grade every question.');
				return $this->render('BioExamBundle:Public:grade.html.twig', array('exam' => $exam, 'taker' => $target, 'title' => 'Grade Exam'));
			}

			// make sure all ids in form match questions in exam
			foreach($request->request->keys() as $key) {
				if (!$this->arrayContainsId($exam->getQuestions()->toArray(), $key)) {
					$request->getSession()->getFlashBag()->set('failure', 'Error');
					return $this->render('BioExamBundle:Public:grade.html.twig', array('exam' => $exam, 'taker' => $target, 'title' => 'Grade Exam'));
				} else {
					$points[$key] = $request->request->get($key);
				}
			}

			// if target has been graded already, don't overwrite TODO append??
			if (!$target->hasVar('points')){
				$target->setVar('points', $points);
			}
			$taker->setStatus(6);
			$db->close();

			$request->getSession()->getFlashBag()->set('success', 'Completed.');
			return $this->redirect($this->generateUrl('exam_entrance'));

		}

		return $this->render('BioExamBundle:Public:grade.html.twig', array('exam' => $exam, 'taker' => $target, 'title' => 'Grade Exam'));
	}

	/**
	 * @Route("/check.json", name="check")
	 * @Template("BioExamBundle:Public:check.json.twig")
	 */
	public function checkAction(Request $request) {

		// check if proper request
		if ($request->request->has('sid') && $request->request->has('exam')) {

			// get the person
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$you = $db->findOne(array('sid' => $request->request->get('sid'), 'exam' => $request->request->get('exam')));

			// if you don't exist
			if (!$you) {
				return array('error' => true, 'message' => 'Cannot find entry with that sid and exam.');
			}

			// if they got assigned someone between checks
			if ($you->getGrader() !== '') {
				return array('error' => false, 'message' => "HOORAY", 'sid' => $you->getGrader());
			}

			// get everyone else

			// if they force it
			if ($request->request->has('force') && $request->request->get('force') === 'force'){

				// get everyone who's finished a test and at least started grading
				$em = $this->getDoctrine()->getManager();
				$query = $em->createQueryBuilder()
					->select('p')
					->from('BioExamBundle:TestTaker', 'p')
					->where('p.status >= 5')
					->andWhere('p.exam = :eid')
					->setParameter('eid', $request->request->get('exam'))
					->getQuery();
				$targets = $query->getResult();

				// if there's literally nobody
				if (count($targets) === 0) {
					return array('error' => true, 'message' => 'No other test takers.');
				}

				// choose person randomly
				$target = $targets[rand(0, count($targets) - 1)];

			// if they ask nicely
			} else {

				// get everyone unpaired
				$targets = $db->find(array('status' => 4, 'exam' => $request->request->get('exam'), 'grader' => ''), array(), false);

				// get person who's not yourself from targets
				if (count($targets) < 2) {
					return array('error' => true, 'message' => 'No other test takers.');
				} else {
					if ($targets[0]->getSid() === $you->getSid()) {
						$target = $targets[1];
					} else {
						$target = $targets[0];
					}
				}


			// make a cute pairing
				$target->setGrader($you->getSid());
			}
			$you->setGrader($target->getSid());
			$db->close();
			

			return array('error' => false, 'message' => "HOORAY", 'sid' => $you->getGrader());
		}

		return array('error' => true, 'message' => 'Improper request.');

	}

	private function arrayContainsId($array, $id) {
		foreach($array as $object) {
			if ($object->getId() === $id) {
				return true;
			}
		}
		return false;
	}

	private function getNextExam() {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
				'SELECT p FROM BioExamBundle:Exam p
		 		 WHERE p.date >= CURRENT_DATE()
				 AND p.end >= CURRENT_TIME()
				 ORDER BY p.date ASC, p.start ASC'
			);
		$result = $query->getResult();

		if (count($result) === 0) {
			throw new BioException('No more scheduled exams.');
		} else {
			$exam = $result[0];
		}

		$diff = date_diff($exam->getDate(), new \DateTime());
		if ($diff->days > 0) {
			throw new BioException('No scheduled exam in the next 24 hours.');
		} else {
			return $exam;
		}
	}
}