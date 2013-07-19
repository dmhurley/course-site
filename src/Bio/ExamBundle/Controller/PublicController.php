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
	 * @Template()
	 */
	public function signAction(Request $request) {
		// create signin form
		$form = $this->createFormBuilder()
			->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false)) // add a pattern to this TODO
			->add('lName', 'text', array('label' => 'Last Name:', 'mapped' => false))
			->add('sign in', 'submit')
			->getForm();

		// check to see if there is an exam
		try {
			$array = $this->check(true);
		} catch (BioException $e) {
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return array('form' => $form->createView(), 'title' => 'Log In');
		}

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
					return array('form' => $form->createView(), 'title' => 'Log In');
				}

				// check to see if they've already logged in
				// add to database if they haven't
				$db = new Database($this, 'BioExamBundle:TestTaker');
				$taker = $db->findOne(array('sid' => $form->get('sid')->getData(), 'exam' => $array['exam']->getId()));
				if (!$taker) {
					$taker = new TestTaker();
					$taker->setSid($form->get('sid')->getData())
						->setExam($array['exam']->getId())
						->setStatus(1);
					$db->add($taker);
					$db->close();
				}

				// overwrite session in case multiple people go in succession
				$session = $request->getSession();
				$session->set('sid', $taker->getSid());
				$session->set('eid', $array['exam']->getId());


				// route to page based on status
				if ($taker->getStatus() === 1) {

					$request->getSession()->getFlashBag()->set('success', 'Signed in.');
					return $this->redirect($this->generateUrl('exam_start'));
				} else if ($taker->getStatus() === 2) {

					$request->getSession()->getFlashBag()->set('success', 'You previously started the test.');
					return $this->redirect($this->generateUrl('exam_take'));
				} else if ($taker->getStatus() === 3) {

					$request->getSession()->getFlashBag()->set('success', 'Review your answers and submit.');
					return $this->redirect($this->generateUrl('exam_review'));
				} else if ($taker->getStatus() == 4) {

					$request->getSession()->getFlashBag()->set('success', 'You have already finished your exam. Please wait to grade a test.');
					return $this->redirect($this->generateUrl('exam_confirm'));
				} else if ($taker->getStatus() == 5) {

					$request->getSession()->getFlashBag()->set('success', 'You still need to grade this test.');
					return $this->redirect($this->generateUrl('exam_grade'));
				} else if ($taker->getStatus() == 6) {

					$request->getSession()->getFlashBag()->set('success', 'You have already completed the exam');
					return $this->redirect($this->generateUrl('main_page'));
				}
			}
		}

		return array('form' => $form->createView(), 'title' => 'Sign In');
	}

	/**
	 * @Route("/readyset", name="exam_start")
	 * @Template()
	 */
	public function startAction(Request $request) {
		// check for exam soon, signed in, and haven't started yet
		try {
			$array = $this->check(true, $request, 1);
		} catch (BioException $e) {
			// redirect forward if they have started already
			if ($e->getMessage() > 1) {
				return $this->redirect($this->generateUrl('exam_take'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		// check to see if they pressed the start button
		if ($request->getMethod() === "POST") {

			// check to see if they started on team
			if ($array['exam']->getStart() <= new \DateTime()) {
				$array['taker']->setStatus(2);
				$array['taker']->setVar('started', new \DateTime());
				$array['db']->close();
				return $this->redirect($this->generateUrl('exam_take'));
			} else {

				// if they got to this early
				$request->getSession()->getFlashBag()->set('failure', "Exam has not started yet.");
			}
		}

		return array('form' => $form->createView(), 'exam' => $array['exam'], 'title' => 'Begin Test');
	}

	/**
	 * @Route("/go", name="exam_take")
	 * @Template()
	 */
	public function examAction(Request $request) {
		// check for exam soon, signed in, and haven't started yet
		try {
			$array = $this->check(true, $request, 2);
		} catch (BioException $e) {
			// redirect forward if they're passed this
			// redirect back if they haven't gotten here yet
			if ($e->getMessage() > 2) {
				return $this->redirect($this->generateUrl('exam_review'));
			} else if ($e->getMessage() < 2) {
				return $this->redirect($this->generateUrl('exam_start'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		// if they pressed submit
		if ($request->getMethod() === "POST") {
			$answers = array();

			// add their answers to array by key value IF the keys match the exam
			foreach($request->request->keys() as $key) {
				if ($this->arrayContainsId($array['exam']->getQuestions()->toArray(), $key)){
					$answers[$key] = $request->request->get($key);
				} else {
					$request->getSession()->getFlashBag()->set('failure', 'Error');
					return array('exam' => $array['exam'], 'taker' => $array['taker'], 'title' => $array['exam']->getName());
				}
			}

			// save stuff
			$array['taker']->setVar('answers', $answers);
			$array['taker']->setStatus(3);
			$array['db']->close();

			return $this->redirect($this->generateUrl('exam_review'));
		}
		return array('exam' => $array['exam'], 'taker' => $array['taker'], 'title' => $array['exam']->getName());
	}

	/**
	 * @Route("/review", name="exam_review")
	 * @Template()
	 */
	public function reviewAction(Request $request) {
		// check for exam, see if signed in, check status
		try {
			$array = $this->check(true, $request, 3);
		} catch (BioException $e) {
			// do redirects if necessary
			if ($e->getMessage() > 3) {
				return $this->redirect($this->generateUrl('exam_confirm'));
			} else if ($e->getMessage() < 3) {
				return $this->redirect($this->generateUrl('exam_take'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		$form = $this->createFormBuilder()
			->add('edit', 'submit')
			->add('submit', 'submit')
			->getForm();

		// if they pressed a button
		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);

			// if they want to edit stuff
			if ($form->get('edit')->isClicked()) {
				$array['taker']->setStatus(2);
				$array['taker']->setVar('edited', true); // save that they edited it
				$array['db']->close();
				return $this->redirect($this->generateUrl('exam_take'));

			// if they want to submit it
			} else {
				$array['taker']->setStatus(4);
				$array['taker']->setVar('ended', new \DateTime());

				// check to see if they went over the time limit, let them continue working. Just marks it.
				$diff = date_diff($array['taker']->getVar('ended'), $array['taker']->getVar('started'));
				$duration = new \DateInterval('PT'.$array['exam']->getDuration()."M");
				if ($diff->format('%Y-%M-%D %H:%I:%S') > $duration->format('%Y-%M-%D %H:%I:%S') ){
					$request->getSession()->getFlashBag()->set('success', 'Went over time limit. Answers saved.');
					$array['taker']->setVar('error', -1);
				} else {
					$request->getSession()->getFlashBag()->set('success', 'Answers saved.');
				}
				$array['db']->close();
				return $this->redirect($this->generateUrl('exam_confirm'));
			}
		}

		return array('form'=>$form->createView(), 'exam' => $array['exam'], 'taker' => $array['taker'], 'title' => 'Review Answers.');
	}

	/**
	 * @Route("/confirmation", name="exam_confirm")
	 * @Template()
	 */
	public function confirmationAction(Request $request) {
		// checks exam still going, logged in, right status
		try {
			$array = $this->check(true, $request, 4);
		} catch (BioException $e) {
			if ($e->getMessage() > 4) {
				return $this->redirect($this->generateUrl('exam_grade'));
			} else if ($e->getMessage() < 4) {
				return $this->redirect($this->generateUrl('exam_take'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		// if they pressed the grade button
		if ($request->getMethod() === "POST") {
			// if they haven't cheated set them up and forward them
			if ($array['taker']->getGrader() !== '') {
				$array['taker']->setStatus(5);
				$array['db']->close();
				return $this->redirect($this->generateUrl('exam_grade'));
			}
		}

		return array('taker' => $array['taker'], 'exam' => $array['exam'], 'title' => 'Exam Submitted');
	}

	/**
	 * @Route("/grade", name="exam_grade")
	 * @Template()
	 */
	public function gradeAction(Request $request) {
		// check if exam is still going, if logged in, status
		try {
			$array = $this->check(true, $request, 5);
		} catch (BioException $e) {
			if ($e->getMessage() > 5) {
				return $this->redirect($this->generateUrl('main_page'));
			} else if ($e->getMessage() < 5) {
				return $this->redirect($this->generateUrl('exam_confirm'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		// finds the person they're grading
		$db = new Database($this, 'BioExamBundle:TestTaker');
		$target = $db->findOne(array('sid' => $array['taker']->getGrader()));

		$exam = $array['exam'];

		// if they pressed submit
		if ($request->getMethod() === "POST") {

			// get the points they assigned from the form

			$points = array();

			// make sure they graded everything
			if (count($request->request->keys()) !== count($exam->getQuestions())) {
				$request->getSession()->getFlashBag()->set('failure', 'You did not grade every question.');
				return array('exam' => $exam, 'taker' => $target, 'title' => 'Grade Exam');
			}

			// make sure all ids in form match questions in exam
			foreach($request->request->keys() as $key) {
				if (!$this->arrayContainsId($exam->getQuestions()->toArray(), $key)) {
					$request->getSession()->getFlashBag()->set('failure', 'Error');
					return array('exam' => $exam, 'taker' => $target, 'title' => 'Grade Exam');
				} else {
					$points[$key] = $request->request->get($key);
				}
			}

			// if target has been graded already, don't overwrite TODO append??
			if (!$target->hasVar('points')){
				$target->setVar('points', $points);
			}
			$array['taker']->setStatus(6);
			$db->close();

			$request->getSession()->getFlashBag()->set('success', 'Completed exam and grading.');
			return $this->redirect($this->generateUrl('main_page'));

		}

		return array('exam' => $exam, 'taker' => $target, 'title' => 'Grade Exam');
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

	private function check($isExam = true, Request $loggedIn = null, $status = null) {
		$array = array();

		if ($isExam) {
			$array['exam'] = $this->getNextExam();
		}

		if ($loggedIn) {
			$session = $loggedIn->getSession();
			if ($session->has('sid') && $session->has('eid')) {
				$db = new Database($this, 'BioExamBundle:TestTaker');
				$array['db'] = $db;
				$taker = $db->findOne(array('sid' => $session->get('sid'), 'exam' => $array['exam']->getId()));
				if (!$taker) {
					throw new BioException("Not logged in.");
				}
				$array['taker'] = $taker;
			} else {
				throw new BioException("Not logged in.");
			}
		}

		if ($status) {
			if ($array['taker']->getStatus() !== $status) {
				throw new BioException($array['taker']->getStatus());
			}
			$array['status'] = $status;
		}

		return $array;
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