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
			->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false))
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
				// reroute if they have
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

				$session = $request->getSession();
				$session->set('sid', $taker->getSid());
				$session->set('eid', $array['exam']->getId());

				if ($taker->getStatus() === 1) {
					// $request->getSession()->getFlashBag()->set('success', 'Signed in.');
					return $this->redirect($this->generateUrl('exam_start'));
				} else if ($taker->getStatus() === 2) {
					return $this->redirect($this->generateUrl('exam_take'));
				} else if ($taker->getStatus() === 3) {
					return $this->redirect($this->generateUrl('exam_review'));
				} else if ($taker->getStatus() == 4) {
					return $this->redirect($this->generateUrl('exam_confirm'));
				}
				/// dot dot dot
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

			// check to see if they cheated and did it early
			if ($array['exam']->getStart() > new \DateTime()) {
				$request->getSession()->getFlashBag()->set('failure', "Exam has not started yet.");
				return $this->redirect($this->generateUrl('exam_start'));
			}

			$array['taker']->setStatus(2);
			$array['taker']->setVar('started', new \DateTime());
			$array['db']->close();

			return $this->redirect($this->generateUrl('exam_take'));
		}

		return array('form' => $form->createView(), 'exam' => $array['exam'], 'title' => 'Begin Test');
	}

	/**
	 * @Route("/go", name="exam_take")
	 * @Template()
	 */
	public function examAction(Request $request) {
		try {
			$array = $this->check(true, $request, 2);
		} catch (BioException $e) {
			if ($e->getMessage() > 2) {
				return $this->redirect($this->generateUrl('exam_review'));
			} else if ($e->getMessage() < 2) {
				return $this->redirect($this->generateUrl('exam_start'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		// should be unnecessary due to statussss
		// // check to see if they cheated and did it early
		// if ($array['exam']->getStart() > new \DateTime()) {
		// 	$request->getSession()->getFlashBag()->set('failure', "Exam has not started yet.");
		// 	return $this->redirect($this->generateUrl('exam_start'));
		// }

		if ($request->getMethod() === "POST") {
			$answers = array();
			$db = new Database($this, 'BioExamBundle:Question');

			foreach($request->request->keys() as $key) {
				if ($db->findOne(array('id' => $key))){
					$answers[$key] = $request->request->get($key);
				} else {
					throw new \Exception("NOOOOOO");
				}
			}
			$array['taker']->setVar('answers', $answers);
			$array['taker']->setStatus(3);
			$db->close();

			return $this->redirect($this->generateUrl('exam_review'));
		}
		$answers = array_key_exists('answers', $array['taker']->getVars())?$array['taker']->getVar('answers'):array();
		return array('exam' => $array['exam'], 'answers' => $answers, 'started' => $array['taker']->getVar('started') , 'title' => $array['exam']->getName());
	}

	/**
	 * @Route("/review", name="exam_review")
	 * @Template()
	 */
	public function reviewAction(Request $request) {
		try {
			$array = $this->check(true, $request, 3);
		} catch (BioException $e) {
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

		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);

			if ($form->get('edit')->isClicked()) {
				$array['taker']->setStatus(2);
				$array['db']->close();
				return $this->redirect($this->generateUrl('exam_take'));
			} else {
				$array['taker']->setStatus(4);
				$array['taker']->setVar('ended', new \DateTime());
				$diff = date_diff($array['taker']->getVar('ended'), $array['taker']->getVar('started'));
				$duration = new \DateInterval('PT'.$array['exam']->getDuration()."M");
				if ($diff->format('%Y-%M-%D %H:%I:%S') > $duration->format('%Y-%M-%D %H:%I:%S') ){
					$request->getSession()->getFlashBag()->set('success', 'Went over time limit. Answers saved.');
					$array['taker']->setVar('error', -2);
				} else if (false) { // if current time is after ending time
					$request->getSession()->getFlashBag()->set('success', 'Submitted late. Answers saved.');
					$array['taker']->setVar('error', -1);
				} else {
					$request->getSession()->getFlashBag()->set('success', 'Answers saved.');
				}

				$array['db']->close();
				return $this->redirect($this->generateUrl('exam_confirm'));
			}
		}

		return array('form'=>$form->createView(), 'exam' => $array['exam'], 'started' => $array['taker']->getVar('started'), 'answers' => $array['taker']->getVar('answers'), 'title' => 'Review Answers.');
	}

	/**
	 * @Route("/confirmation", name="exam_confirm")
	 * @Template()
	 */
	public function confirmationAction(Request $request) {
		try {
			$array = $this->check(true, $request, 4);
		} catch (BioException $e) {
			if ($e->getMessage() > 4) {
				return $this->redirect($this->generateUrl('main_page'));
			} else if ($e->getMessage() < 4) {
				return $this->redirect($this->generateUrl('exam_take'));
			}
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		return array( 'title' => 'Exam Submitted', 'taker' => $array['taker']);
	}

	/**
	 * @Route("/check.json", name="check")
	 * @Template("BioExamBundle:Public:check.json.twig")
	 */
	public function checkAction(Request $request) {
		if ($request->request->has('sid') && $request->request->has('exam')) {
			// get the person
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$you = $db->findOne(array('sid' => $request->request->get('sid'), 'exam' => $request->request->get('exam')));

			if ($you->getGrader() !== '') {
				return array('error' => false, 'message' => "HOORAY", 'sid' => $you->getGrader());
			}

			// get everyone else
			if ($request->request->has('force') && $request->request->get('force') === 'force'){
				$targets = $db->find(array('status' => 4, 'exam' => $request->request->get('exam')), array(), false);
				if (count($targets) < 2) {
					return array('error' => true, 'message' => 'No other test takers.');
				}
				$target = $targets[rand(0, count($targets) - 1)];
			} else {
				$targets = $db->find(array('status' => 4, 'exam' => $request->request->get('exam'), 'grader' => ''), array(), false);
				if (count($targets) < 2) {
					return array('error' => true, 'message' => 'No other test takers.');
				} else {
					if ($targets[0]->getSid() === $you->getSid()) {
						$target = $targets[1];
					} else {
						$target = $targets[0];
					}
				}
				$target->setGrader($you->getSid());
			}
			$you->setGrader($target->getSid());

			$db->close();

			return array('error' => false, 'message' => "HOORAY", 'sid' => $you->getGrader());
		}

		return array('error' => true, 'message' => 'Improper request.');

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