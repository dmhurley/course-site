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

		// check to see if there is a test soon.
		try {
			$exam = $this->getNextExam();
		} catch (BioException $e) {
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return array('form' => $form->createView(), 'title' => 'Sign In');
		}

		// check to see if they just submitted logon info
		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			$sid = $form->get('sid')->getData();
			$lName = $form->get('lName')->getData();

			// check to see if student exists
			$db =  new Database($this, 'BioStudentBundle:Student');
			$student = $db->findOne(array('sid' => $sid, 'lName' => $lName));
			if ($student) {

				// check if they have already signed in for this test
				$db = new Database($this, 'BioExamBundle:TestTaker');
				$taker = $db->findOne(array('sid' => $sid, 'exam' => $exam->getId()));

				// otherwise create and add to databse
				if (!$taker) {
					$taker = new TestTaker();
					$taker->setStatus(1) // set status signed in
						->setSid($sid)
						->setExam($exam->getId()); // TODO
					$db->add($taker);
					$db->close();
				}

				// create session stuff, overwriting any old stuffs
				$session = $request->getSession();
				$session->set('duration', 30);
				$session->set('start', $exam->getStart());
				$session->set('sid', $sid);
				$session->set('eid', $exam->getId());

				if ($taker->getStatus() === 1){
					return $this->redirect($this->generateUrl('exam_start'));
				} else if ($taker->getStatus() === 2) {
					return $this->redirect($this->generateUrl('exam_take'));
				}
				$request->getSession()->getFlashBag()->set('failure','..... uhhhhh wrong status?'); //TEMPORARY
			} else {
				$request->getSession()->getFlashBag()->set('failure', 'Could not find anyone with that student ID and last name.');
			}
		}
		return array('form' => $form->createView(), 'title' => 'Sign In');
	}

	/**
	 * @Route("/readyset", name="exam_start")
	 * @Template()
	 */
	public function startAction(Request $request) {
		$session = $request->getSession();

		// check to see if they are signed in
		if ($session->has('sid') && $session->has('duration') && $session->has('start') && $session->has('eid')) {

			if ($request->getMethod() === "POST") {

			}
			$start = $session->get('start');
			$form = $this->createFormBuilder()
				->add('start', 'submit')
				->getForm();

			return array('form' => $form->createView(), 'title' => 'Begin');
		} else {
			$session->getFlashBag()->set('failure', 'Not signed in.');
			return $this->redirect($this->generateUrl('exam_entrance'));
		}
	}

	/**
	 * @Route("/go", name="exam_take")
	 * @Template()
	 */
	public function examAction(Request $request) {
		$session = $request->getSession();
		// check to see if there even in an exam
		try {
			$exam = $this->getNextExam();
		} catch (BioException $e) {
			$session->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		// check to see if they accessed the page early
		if ($exam->getStart() > new \DateTime()) {
			$session->getFlashBag()->set('failure', "The exam has not started yet.");
			$session->set('start', $exam->getStart());
			return $this->redirect($this->generateUrl('exam_start'));
		}

		// check to see if they are signed in
		if ($session->has('sid') && $session->has('duration') && $session->has('start') && $session->get('eid') === $exam->getId()) {
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$taker = $db->findOne(array('sid' => $session->get('sid'), 'exam' => $exam->getId()));

			// check to see if student has started test
			// if they haven't, start them
			if ($taker->getStatus() == 1) {
				$taker->setStatus(2);
				$taker->setVar('started', new \DateTime());
				$db->close();
			}



			return array('exam' => $exam, 'title' => $exam->getName(), 'started' => $taker->getVar('started'));
		} else {
			$session->getFlashBag()->set('failure', 'Not signed in.');
			return $this->redirect($this->generateUrl('exam_entrance'));
		}
	}

	/**
	 * @Route("/review", name="review_exam")
	 * @Template()
	 */
	public function reviewAction(Request $request) {
		if ($request->getMethod() === "POST") {
			$session = $request->getSession();
			
			// check to see if signed in
			if (!$session->has('sid') || !$session->has('duration') || !$session->has('start') || !$session->has('eid')) {
				$session->getFlashBag()->set('failure', 'Not signed in.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			}

			// check to see if exam exists
			$db = new Database($this, 'BioExamBundle:Exam');
			$exam = $db->findOne(array('id' => $session->get('eid')));
			if (!$exam) {
				return $this->redirect($this->generateUrl('exam_entrance'));
			}

			// check to see if they have started the test
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$taker = $db->findOne(array('sid' => $session->get('sid'), 'exam' => $session->get('eid')));
			if (!$taker) {
				$session->getFlashBag()->set('failure', 'You have not taken the test yet.');
				return $this->redirect($this->generateUrl('exam_start'));
			}

			$db = new Database($this, 'BioExamBundle:Question');
			$answers = array();
			$questions = array();
			foreach ($request->request->keys() as $key) {
				$answers[$key] = $request->request->get($key);
				$questions[$key] = $db->findOne(array('id' => $key));
			}
			$taker->setVar('answers', $answers);
			$db->close();

			return array('questions' => $questions, 'answers' => $answers, 'title' => 'Review Answers');
		}
		if ($request->headers->get('referer')) {
			return $this->redirect($request->headers->get('referer'));
		} else {
			return $this->redirect($this->generateUrl('exam_take'));
		}
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