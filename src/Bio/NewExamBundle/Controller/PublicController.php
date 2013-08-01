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
use Bio\NewExamBundle\Entity\TestTaker;
use Bio\NewExamBundle\Entity\Answer;

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
			$taker = $db->findOne(array('student' => $session->get('student'), 'exam' => $exam));

			if ($taker) {

				if ($taker->getStatus() === 1) {
					return $this->startAction($request, $exam, $taker);
				}

				if ($taker->getStatus() === 2) {
					return $this->examAction($request, $exam, $taker);
				}

				if ($taker->getStatus() === 3) {
					return $this->reviewAction($request, $exam, $taker);
				}

				if ($taker->getStatus() === 4) {
					return $this->waitAction($request, $exam, $taker);
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

				// see if they've already started the exam
				$db = new Database($this, 'BioNewExamBundle:TestTaker');
				$taker = $db->findOne(array('student' => $student, 'exam' => $exam));

				// if not, create and add
				if (!$taker) {
					$taker = new TestTaker();
					$taker->setStatus(1)
						->setStudent($student)
						->setExam($exam);

					$db->add($taker);	
					$db->close();
				}

				// make session and set flash message
				$request->getSession()->invalidate();
				$request->getSession()->set('student', $student);
				$request->getSession()->getFlashBag()->set('success', 'Signed in.');

				return $this->redirect($this->generateUrl('exam_entrance'));
			} else {
				$request->getSession()->getFlashBag()->set('failure', 'Could not find anyone with that last name or student ID.');
			}
		}


		return $this->render('BioNewExamBundle:Public:sign.html.twig', array('form' => $form->createView(), 'title' => 'Sign In'));
	}

	/**
	 * Counts down to test start, then makes start button available
	 * 
	 * status 1
	 */
	private function startAction(Request $request, $exam, $taker) {
		// create form
		$form = $this->createFormBuilder()
			->add('start', 'submit')
			->getForm();

		// if they pressed submit
		if ($request->getMethod() === "POST") {

			// if the exam has started
			if ($exam->getStart() <= new \DateTime()) {
				$db = new Database($this, 'BioNewExamBundle:TestTaker'); // so I can close it
				$taker->setStatus(2);
				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Exam started.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			} else {
				// INSPECT ELEMENT CHEATERS GO HERE!
				$request->getSession()->getFlashBag()->set('failure', 'Exam has not started yet.');
			}
		}
		return $this->render('BioNewExamBundle:Public:start.html.twig', array('form' => $form->createView(), 'exam' => $exam, 'title' => 'Begin Test'));
	}

	/**
	 * Allows user to take exam and submit answers
	 * status 2
	 *
	 * @Template()
	 */
	private function examAction(Request $request, $exam, $taker) {
		// if they submitted the exam
		if ($request->getMethod() === "POST") {
			$db = new Database($this, 'BioNewExamBundle:Answer');
			// if the number of keys match up
			if (count($request->request->keys()) === count($exam->getQuestions())){
				foreach($request->request->keys() as $key) {
					echo "hey";
					// check if the question ids match the questions in the exam
					if ($questionArray = $exam->getQuestions()->filter(function($q) use ($key) {
						return $q->getId() === $key;
					})->toArray()){
						// check if the taker has already answered the question
						reset($questionArray);
						$question = current($questionArray);
						if ($answerArray = $taker->getAnswers()->filter(function($a) use ($question) {
							return $a->getQuestion() === $question;
						})->toArray()) {
							// if they have update the answer
							reset($answerArray);
							$answer = current($answerArray);
							$answer->setAnswer($request->request->get($key));
						} else {
							// if they haven't update the answer
							$answer = new Answer();
							$answer->setQuestion($question)
								->setAnswer($request->request->get($key))
								->setTestTaker($taker);
							$taker->addAnswer($answer);
							$db->add($answer);
						}
					} else {

						// if they don't match, fail
						$request->getSession()->getFlashBag()->set('failure', 'Error');
						return $this->render('BioNewExamBundle:Public:exam.html.twig', array('exam' => $exam, 'taker' => $taker, 'title' => $exam->getName()));
					}
				}
				$taker->setStatus(3);
				$db->close();
				return $this->redirect($this->generateUrl('exam_entrance'));
			} else {
				$request->getSession()->getFlashBag()->set('failure', 'You need to fill out every question.');
			}
		}
		return $this->render('BioNewExamBundle:Public:exam.html.twig', array('exam' => $exam, 'taker' => $taker, 'title' => $exam->getTitle()));
	}

	/**
	 * Allows user to review exam and either go back to change or submit answers
	 *
	 * status 3
	 */
	private function reviewAction(Request $request, $exam, $taker) {
		// make form
		$form = $this->createFormBuilder()
			->add('edit', 'submit')
			->add('submit', 'submit')
			->getForm();

		// if they pressed a button (doesn't matter which)
		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			$db = new Database($this, 'BioNewExamBundle:TestTaker');

			// if they want to edit their answers
			if ($form->get('edit')->isClicked()) {
				$taker->setStatus(2); // decrement status
				$taker->setVar('edited', true); // save that they edited it
				$db->close();
				$request->getSession()->getFlashBag()->set('success', 'Answers saved.');
				return $this->redirect($this->generateUrl('exam_entrance'));

			// if they want to submit their answers
			} else if ($form->get('submit')->isClicked()) {
				$taker->setStatus(4); // increment
				$db->close();

				$request->getSession()->getFlashBag()->set('success', 'Exam submitted.');
				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioNewExamBundle:Public:review.html.twig', array('form' => $form->createView(), 'taker' => $taker, 'exam' => $exam));
	}

	/**
	 * User waits here until they have someone to grade
	 *
	 * status 4
	 */
	private function waitAction(Request $request, $exam, $taker) {
		// if they pressed the grade button
		$target = null;
		try {
			$target = $this->match($taker);
		} catch (BioException $e) {
		}

		// if the pressed submit
		if ($request->getMethod() === "POST") {
			// if there actually was a match
			if ($target !== null) {
				$db = new Database($this, 'BioNewExamBundle:TestTaker');
				$taker->setStatus(5)
					->setGrading($target);
				$db->close();

				return $this->redirect($this->generateUrl('exam_entrance'));
			}
		}

		return $this->render('BioNewExamBundle:Public:wait.html.twig', array('taker' => $taker, 'exam' => $exam, 'title' => 'Exam Submitted'));
	}

	/**
	 * User grades another users' test, status is incremented up/down depending on tests graded
	 *
	 * status 5
	 */
	private function gradeAction(Request $request, $exam, $taker) {

	}

	/**
	 * Recieves a post request containing student_id, exam_id. Attempts to find someone for user to grade
	 *
	 * @Route("/check?a={$", name="check")
	 * @Template("BioNewExamBundle:Public:check.json.twig")
	 */
	public function checkAction(Request $request) {
		if ($request->request->has('a')) {
			$id = $request->request->get('a');

			$db = new Database($this, 'BioNewExamBundle:TestTaker');
			$you = $db->findOne(array('id' => $id));

			if (!$you) {
				return array('success' => false, 'message' => 'Invalid Id.');
			}

			try {
				$this->match($you);
			} catch (BioException $e) {
				return array('success' => false, 'message' => $e->getMessage());
			}
			return array('success' => true);
		}
		return array('success' => false, 'message' => 'Invalid post request.');
	}

	private function match($you) {
		if ($you->getGrading() ) {
			return $you->getGrading();
		}

		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery('
				SELECT t, COUNT(a) as c
				FROM BioNewExamBundle:TestTaker t 
				LEFT JOIN t.answers a 
				WHERE t.exam = :exam
				AND t.id <> :id
				AND t.status >= 4
				GROUP BY t.id
				ORDER BY c
			');
		// do we need group by ^

		$query->setParameter('exam', $you->getExam());
		$query->setParameter('id', $you->getId());

		$targets = $query->getResult();

		if (count($targets) === 0) {
			throw new BioException("No other tests.");
		}

		$target = null;
		for ($i = 0; $i < count($targets); $i++) {
			if (!$you->getGraded()->contains($targets[$i]) ) {
				$target = $targets[$i];
				break;
			}
		}

		if ($target === null) {
			throw new BioException("No other tests.");
		}
		return $target[0];
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