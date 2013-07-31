<?php

namespace Bio\NewExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;
use Bio\NewExamBundle\Entity\Exam;
use Bio\NewExamBundle\Entity\Question;

/**
 * @Route("/exam")
 */
class PublicController extends Controller
{
	/**
	 * @route("", name="exam_entrance")
	 */
	public function indexAction(Request $request) {
		$session = $request->getSession();
		$flash = $session->getFlashBag();

		// see if there is an exam within the next 24 hours
		// if not, redirect to main page with error
		try {
			$exam = $this->getNextExam();
		} catch (BioException $e) {
			$flash->set('failure', $e->getMessage());
			return $this->redirect($this->generateUrl('main_page'));
		}

		// if url ends with ?logout, sign user out
		if ($request->query->has('logout')) {
			$session->invalidate();
		}

		// if signed in
		if ($session->has('student')) {

			// get the appropriate test taker for the student/exam combo
			$db = new Database($this, 'BioNewExamBundle:TestTaker');
			$taker = $db->findOne(array('student' => $student, 'exam' => $exam));

			if ($taker) {

				if ($taker->getStatus() === 1) {
					// redirect to start page
				}

				if ($taker->getStatus() === 2) {
					// redirect to test page
				}

				if ($taker->getStatus() === 3) {
					// redirect to review page
				}

				if ($taker->getStatus() === 4) {
					// redirect to wait for grade page
				}

				if ($taker->getStatus() === 5) {
					// redirect to grade page
				}

				if ($taker->getStatus() === 6) {
					// done! redirect to main page with confirmation
				}
			} else {
				$flash->set('failure', 'Not signed in.');
			}
		}

		return $this->signAction($request, $exam);
	}

	/**
	 * Requests sid and last name of user, creates TestTaker entity and session on success
	 * 
	 * status null
	 */
	private function signAction(Request $request, $exam) {
		// create form
		$form = $this->createFormBuilder()
			->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false, 'attr' => array('pattern' => '[0-9]{7}', 'title' => 'Seven digit student ID.')))
			->add('lName', 'text', array('label' => 'Last Name:', 'mapped' => false))
			->add('sign in', 'submit')
			->getForm();

		// if form was submitted
		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			// get form data
			$sid = $form->get('sid')->getData();
			$lName = $form->get('lName')->getData();

			// find student
			$db = new Database($this, 'BioStudentBundle:Student');
			$student = $db->findOne(array('sid' => $sid, 'lName' => $lName));

			// if student exists
			if ($student) {

				// create and add
				$taker = new TestTaker();
				$taker->setStatus(1)
					->setStudent($student)
					->setExam($exam);

				$db->add($taker);	
				$db->close();

				// make session and set flash message
				$request->getSession()->set('student', $student);
				$request->getSession()->getFlashBag()->set('success', 'Signed in.');

				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}


		return $this->render('BioNewExamBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Sign In'));
	}

	/**
	 * Counts down to test start, then makes start button available
	 * status 1
	 *
	 * @Template()
	 */
	private function startAction(Request $request, $exam, $taker) {

	}

	/**
	 * Allows user to take exam and submit answers
	 * status 2
	 *
	 * @Template()
	 */
	private function examAction(Request $request, $exam, $taker) {

	}

	/**
	 * Allows user to review exam and either go back to change or submit answers
	 * status 3
	 *
	 * @Template()
	 */
	private function reviewAction(Request $request, $exam, $taker) {

	}

	/**
	 * User waits here until they have someone to grade
	 * status 4
	 *
	 * @Template()
	 */
	private function waitAction(Request $request, $exam, $taker) {

	}

	/**
	 * User grades another users' test, status is incremented up/down depending on tests graded
	 * status 5
	 *
	 * @Template()
	 */
	private function gradeAction(Request $request, $exam, $taker) {

	}

	/**
	 * Recieves a post request containing student_id, exam_id. Attempts to find someone for user to grade
	 *
	 * @Route("/check.json", name="check")
	 * @Template()
	 */
	public function checkAction(Request $request) {

	}

	private function getNextExam() {
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQueryBuilder()
			->select('p')
			->from('BioNewExamBundle:Exam', 'p')
			->where('p.date >= :date')
			->andWhere('p.end >= :time')
			->addOrderBy('p.date', 'ASC')
			->addOrderBy('p.start', 'ASC')
			->setParameter('date', new \DateTime(), \Doctrine\DBAL\Types\Type::DATE)
			->setParameter('time', new \DateTime(), \Doctrine\DBAL\Types\Type::TIME)
			->getQuery();
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