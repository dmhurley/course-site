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
	public function startAction(Request $request) {
		$form = $this->createFormBuilder()
			->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false))
			->add('lName', 'text', array('label' => 'Last Name:', 'mapped' => false))
			->add('sign in', 'submit')
			->getForm();

		if ($request->getMethod() === "POST") {
			$form->handleRequest($request);
			$sid = $form->get('sid')->getData();
			$lName = $form->get('lName')->getData();

			// see if student exists in database
			$db =  new Database($this, 'BioStudentBundle:Student');
			$student = $db->findOne(array('sid' => $sid, 'lName' => $lName));
			if ($student) {
				// $session = $request->getSession();
				$db = new Database($this, 'BioExamBundle:TestTaker');
				$taker = $db->findOne(array('sid' => $sid, 'exam' => 1));
				if (!$taker) {
					$taker = new TestTaker();
					$taker->setStatus(1)
						->setSid($sid)
						->setExam(1);
					$db->add($taker);
					$db->close();
				}
			} else {
				$request->getSession()->getFlashBag()->set('failure', 'Could not find anyone with that student ID and last name.');
			}
		}
		return array('form' => $form->createView(), 'title' => 'Sign In');
	}
}