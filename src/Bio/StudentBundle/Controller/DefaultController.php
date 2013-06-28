<?php

namespace Bio\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\StudentBundle\Entity\Student;
use Bio\StudentBundle\Exception\BioException;

/**
 * @Route("/student")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/find", name="find_student")
     * @Template("BioStudentBundle:Default:delete.html.twig")
     */
    public function findAction(Request $request){
        $form = $this->createFormBuilder(new Student())
            ->add('sid', 'integer', array('label' => "Student ID:"))
            ->add('fName', 'text', array('label' => "First Name:", 'read_only' => true))
            ->add('lName', 'text', array('label' => "Last Name:", 'read_only' => true))
            ->add('email', 'email', array('label' => "Email:", 'read_only' => true))
            ->add('Find', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            $sid = $form->get('sid')->getData();

            try {
                $entity = $this->findStudent($sid);
                $request->getSession()->getFlashBag()->set('success', 'Student found!');
                $form = $this->createFormBuilder($entity)
                    ->add('sid', 'integer', array('label' => "Student ID:"))
                    ->add('fName', 'text', array('label' => "First Name:", 'read_only' => true))
                    ->add('lName', 'text', array('label' => "Last Name:", 'read_only' => true))
                    ->add('email', 'email', array('label' => "Email:", 'read_only' => true))
                    ->add('Find', 'submit')
                    ->getForm();

            } catch (BioException $e){
                $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
            }
        }

        return array('form' => $form->createView(), 'title' => 'Find Student');
    }

    /**
     * @Route("/add", name="add_student")
     * @Template()
     */
    public function addAction(Request $request)
    {
    	$entity = new Student();
    	$form = $this->createFormBuilder($entity)
    		->add('sid', 'integer', array('label' => "Student ID:"))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
    		->add('email', 'email', array('label' => "Email:"))
    		->add('Add', 'submit')
    		->getForm();

    	$cloned = clone $form;

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {
    			try {
                    $this->addStudent($entity);
                    $request->getSession()->getFlashBag()->set('success', 'Student added!');
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
                }
	    		$form = $cloned;
    		} else {
    			$request->getSession()->getFlashBag()->set('failure', 'There was an error. Please try again :(');
    		}
    	}
        return array('form' => $form->createView(), 'title' => "Add Student");
    }

    /**
     * @Route("/delete", name="delete_student")
     * @Template()
     */
    public function deleteAction(Request $request) {
    	$form = $this->createFormBuilder(new Student())
    		->add('sid', 'integer', array('label' => "Student ID:"))
    		->add('Delete', 'submit')
    		->getForm();

    	$cloned = clone $form;

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		$sid = $form->get('sid')->getData();

            try {
                $this->deleteStudent($sid);
                $request->getSession()->getFlashBag()->set('success', "Student #".$sid." removed.");
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
            }
    		
    	}

    	return array('form' => $cloned->createView(), 'title' => "Delete Student");
    }

    /**
     * @Route("/edit", name="edit_student")
     * @Template("BioStudentBundle:Default:add.html.twig")
     */
    public function editAction(Request $request) {
    	$entity = new Student();
    	$form = $this->createFormBuilder($entity)
    		->add('sid', 'integer', array('label' => "Student ID:"))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
    		->add('email', 'email', array('label' => "Email:"))
    		->add('Edit', 'submit')
    		->getForm();

    	$cloned = clone $form;

    	if ($request->getMethod() === "POST") {		// if request was sent
    		$form->handleRequest($request);
    		if ($form->isValid()) {					// and form was valid
    			try {
                    $this->editStudent($entity);
                    $request->getSession()->getFlashBag()->set('success', "Student #".$entity->getSid()." updated.");
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
                }
    		}
    	}

    	return array('form' => $form->createView(), 'title' => "Edit Student");
    }
	/**
     * @Route("/upload", name="upload_student")
     * @Template()
     */
    public function uploadAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('file', 'file')
    		->add('Upload', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		$data = $form->get('file')->getData();
    		if ($data !== null) {
    			$file = file($data, FILE_IGNORE_NEW_LINES);
    			try {
                    $this->uploadStudentList($file);
                    $request->getSession()->getFlashBag()->set('success', "Student list updated.");
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
                }
	    	}
    	}
    	return array("form" => $form->createView(), 'title' => "Upload Student List");
    }

    private function findStudent($sid) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioStudentBundle:Student');

        $entity = $repo->findOneBySid($sid);
        if (!$entity) {
            throw new BioException("Student not found.");
        } else {
            return $entity;
        }
    }

    private function addStudent($entity) {
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($entity);
            $em->flush();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new BioException("That Student ID or email is already registered.");
        }
    }

    private function deleteStudent($sid) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioStudentBundle:Student');

        $entity = $repo->findOneBySid($sid);
        if (!$entity) {
            throw new BioException('We could not find a student with that ID.');
        } else {
            $em->remove($entity);
            $em->flush();
        }
    }

    private function editStudent($entity) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioStudentBundle:Student');
        $dbEntity = $repo->findOneBySid($entity->getSid());     // try to find the student in database
        if (!$dbEntity) {                                       // if student does not exist
            throw new BioException("We could not find a student with that ID.");
        } else {                    // student does exist
            try {
                $dbEntity->setFName($entity->getFName());
                $dbEntity->setLName($entity->getLName());
                $dbEntity->setEmail($entity->getEmail());
                // for more changes
                $em->flush();
            } catch (\Doctrine\DBAL\DBALException $e) {
                throw new BioException('A student already has that email.');
            }
        }
    }

    private function uploadStudentList($file) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioStudentBundle:Student');
        $entities = $repo->findAll();

        // truncate table
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('Student', true));

        $sids = [];
        $emails = [];

        for ($i = 1; $i < count($file); $i++) {
            list($sid, $name, $section, $credits, $gender, $class, $major, $email) = preg_split('/","|,"|",|"/', $file[$i], -1, PREG_SPLIT_NO_EMPTY);
            $entity = new Student();
            $entity->setSid($sid);
            $entity->setEmail($email);
            list($lName, $fName) = explode(", ", $name);
            $entity->setFName($fName);
            $entity->setLName($lName);
            if (!in_array($sid, $sids) && !in_array($email, $emails)) {
                $sids[] = $sid;
                $emails[] = $email;
                $em->persist($entity);
            } else {
                $em->clear();
                for ($j = 0; $j < count($entities); $j++) {
                    $em->persist($entities[$j]);
                }
                $em->flush();
                throw new BioException("The file contained duplicate Student IDs or emails.");
            }
        }
        $em->flush();
    }
}
