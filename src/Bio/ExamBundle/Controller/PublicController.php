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
	 * @Route("", name="exam_entrance")
	 */
	public function gateAction(Request $request) {
		$session = $request->getSession();
		$flash = $session->getFlashBag();
		try {
			$exam = $this->getNextExam(); // try
		} catch (BioException $e) {
			$flash->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('main_page'));
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

	//status 1
	private function startAction(Request $request, $exam, $taker, $db) {
		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		// check to see if they pressed the start button
		if ($request->getMethod() === "POST") {

			// check to see if they started on team
			if ($exam->getStart() <= new \DateTime()) {
				$taker->setStatus(2);
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

	// status 2
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
			$taker->setAnswers($answers);
			$taker->setStatus(3);
			$db->close();

			$request->getSession()->getFlashBag()->set('success', 'Answers saved. Please review your answers.');
			return $this->redirect($this->generateUrl('exam_entrance'));
		}
		return $this->render('BioExamBundle:Public:exam.html.twig', array('exam' => $exam, 'taker' => $taker, 'title' => $exam->getName()));
	}

	// status 3
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

				// check to see if they went over the time limit, let them continue working. Just marks it.
				$diff = date_diff($taker->getTimecard(4), $taker->getTimecard(2));
				$duration = new \DateInterval('PT'.$exam->getDuration()."M");
				if ($diff->format('%Y-%M-%D %H:%I:%S') > $duration->format('%Y-%M-%D %H:%I:%S') ){
					$request->getSession()->getFlashBag()->set('success', 'Went over time limit. Answers saved.');
					$taker->setVar('late', true);
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

	// status 4
	private function confirmationAction(Request $request, $exam, $taker, $db) {
		// if they pressed the grade button
		if ($request->getMethod() === "POST") {
			// if they haven't cheated set them up and forward them
			if ( in_array(false, $taker->getGrading()) ) {
				$taker->setStatus(5);
				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Grade this test.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioExamBundle:Public:confirmation.html.twig', array('taker' => $taker, 'exam' => $exam, 'title' => 'Exam Submitted'));
	}

	// status 5
	private function gradeAction(Request $request, $exam, $taker, $db) {
		// finds the person they're grading
		$db = new Database($this, 'BioExamBundle:TestTaker');
		$target = $db->findOne(array('sid' => array_search(false, $taker->getGrading())) );
		// handle not finding target???

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

			$target->addPoint($taker->getSid(), $points);

			$taker->setGrader($target->getSid(), true);

			if ( count($taker->getGrading()) === 2 && !in_array(false, $taker->getGrading()) ) {
					$taker->setStatus(6);
			} else {
				$request->getSession()->getFlashBag()->set('success', 'Exam Graded. Grade '.(2-count($taker->getGrading())).' more.' );
				$taker->setStatus(4);
			}
			$db->close();
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
		if ($request->request->has('id') && $request->request->has('sid') && $request->request->has('exam')) {
			$id = $request->request->get('id');
			$sid = $request->request->get('sid');
			$exam = $request->request->get('exam');

			// get the person
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$you = $db->findOne(array('id' => $id, 'sid' => $sid, 'exam' => $exam));

			// if you don't exist
			if (!$you) {
				return array('error' => true, 'message' => 'Invalid credentials.');
			}

			// if they have someone to grade, find first
			if ( ($grader = array_search(false, $you->getGrading())) !== false ) {
				return array('error' => false, 'message' => "HOORAY", 'sid' => $grader );
			}

			// get all test takers who have finished there test, are taking the asked for exam, and are not the person asking
			// order by status code ascending first, breaking ties by times graded.
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQueryBuilder()
				->select('p')
				->from('BioExamBundle:TestTaker', 'p')
				->where('p.status >= 4')
				->andWhere('p.exam = :exam')
				->andWhere('p.id <> :id')
				//order people by amount of people grading them
				// a serialized array starts with 'a:<length>:{stuff inside array....' so string comparison
				// works as long as <length> doesn't go about 9, or else the string 9 will come before 10
				->addOrderBy('p.points', 'ASC')
				->setParameter('exam', $exam)
				->setParameter('id', $id)
				->getQuery();
			$targets = $query->getResult();

			if (count($targets) === 0) {
				return array('error' => true, 'message' => 'No tests to grade.');
			}

			$target = $targets[0];
			$index = 0;
			// if you've already graded them, get the next
			while(array_key_exists($targets[$index]->getSid(), $you->getGrading()) ) {
				try {
					$target = $targets[++$index];
				} catch(\Exception $e) {
					return array('error' => true, 'message' => "No one left to grade");
				}
			}

			$you->setGrader($target->getSid(), false);
			$target->addPoint($you->getSid(), null);
			$db->close();
			return array('error' => false, 'message' => 'Test Found', 'sid' => $target->getSid());

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