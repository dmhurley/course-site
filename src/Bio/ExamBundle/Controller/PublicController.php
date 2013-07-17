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
		$form = $this->createFormBuilder()
			->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false))
			->add('lName', 'text', array('label' => 'Last Name:', 'mapped' => false))
			->add('sign in', 'submit')
			->getForm();

		try {
			$exam = $this->getNextExam();
		} catch (BioException $e) {
			$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
			return array('form' => $form->createView(), 'title' => 'Sign In');
		}

		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			$sid = $form->get('sid')->getData();
			$lName = $form->get('lName')->getData();

			// see if student exists in database
			$db =  new Database($this, 'BioStudentBundle:Student');
			$student = $db->findOne(array('sid' => $sid, 'lName' => $lName));
			if ($student) {
				$db = new Database($this, 'BioExamBundle:TestTaker');
				// get test taker if they exist
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

				// create session stuff
				$session = $request->getSession();
				$session->set('duration', 30);
				$session->set('sid', $sid);

				return $this->redirect($this->generateUrl('exam_start'));
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

		if ($session->has('sid') && $session->has('duration')) {
			$form = $this->createFormBuilder()
				->setAction($this->generateUrl('exam_take'))
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
		try {
			$exam = $this->getNextExam();
		} catch (BioException $e) {
			$session->getFlashBag()->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('exam_entrance'));
		}

		if ($session->has('sid') && $session->has('duration')) {
			$db = new Database($this, 'BioExamBundle:TestTaker');
			$taker = $db->findOne(array('sid' => $session->get('sid'), 'exam' => $exam->getId()));
			$taker->setStatus(2);
			$db->close();

			return array('exam' => $exam, 'title' => $exam->getName());
		} else {
			$session->getFlashBag()->set('failure', 'Not signed in.');
			return $this->redirect($this->generateUrl('exam_entrance'));
		}
	}

	private function getNextExam() {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
				'SELECT p FROM BioExamBundle:Exam p
		 		 WHERE p.date >= CURRENT_DATE()
				 AND p.start >= CURRENT_TIME()
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