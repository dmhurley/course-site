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
            ->add('sid', 'text', array('label' => "Student ID:", 'required' => false))
            ->add('fName', 'text', array('label' => "First Name:", 'required' => false))
            ->add('lName', 'text', array('label' => "Last Name:", 'required' => false))
            ->add('email', 'email', array('label' => "Email:", 'required' => false))
            ->add('Find', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $values = $request->request->get('form');
            $array = $this->generateArray($values['sid'], $values['fName'], $values['lName'], $values['email']);
            try {
                $entities = $this->findStudent($array, 'sid');
                $request->getSession()->getFlashBag()->set('success', 'Students found!');

                $get = "?filter=";
                if ($values['sid']) {
                    $get.=$values['sid'];
                }
                $get.="-";
                if ($values['fName']) {
                    $get.=$values['fName'];
                }
                $get.="-";
                if ($values['lName']) {
                    $get.=$values['lName'];
                }
                $get.="-";
                if ($values['email']) {
                    $get.=$values['email'];
                }

                return $this->redirect($this->generateUrl('display_students').$get);
            } catch (BioException $e){
                $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
            }
        }

        return array('form' => $form->createView(), 'title' => 'Find Student');
    }

    private function generateArray($sid = null, $fName = null, $lName = null, $email = null) {
        $array = array();
            if ($sid) {
                $array['sid'] = $sid;
            }
            if ($fName) {
                $array['fName'] = $fName;
            }
            if ($lName) {
                $array['lName'] = $lName;
            }
            if ($email) {
                $array['email'] = $email;
            }
        return $array;
    }

    /**
     * @Route("/add", name="add_student")
     * @Template()
     */
    public function addAction(Request $request)
    {
    	$entity = new Student();
    	$form = $this->createFormBuilder($entity)
    		->add('sid', 'text', array('label' => "Student ID:"))
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
     */
    public function deleteAction(Request $request) {
    	if ($request->getMethod() === "GET" && $request->query->get('sid')) {
    		$sid = $request->query->get('sid');

            try {
                $this->deleteStudent($this->findStudent(array('sid' => $sid)));
                $request->getSession()->getFlashBag()->set('success', "Student #".$sid." removed.");
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', "Could not find student #".$sid.".");
            }
    		
    	}
        if (!$request->headers->get('referer')){
            return $this->redirect($this->generateUrl('display_students'));
        } else {
            return $this->redirect($request->headers->get('referer'));
        }
    }

    /**
     * @Route("/edit", name="edit_student")
     * @Template("BioStudentBundle:Default:add.html.twig")
     */
    public function editAction(Request $request) {
        $entity = new Student();
        if ($request->getMethod() === "GET" && $request->query->get('sid')) {
            try {
                $entity = $this->findStudent(array('sid' => $request->query->get('sid')))[0];
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
            }
        }

    	$form = $this->createFormBuilder($entity)
    		->add('sid', 'text', array('label' => "Student ID:", 'read_only'=> true))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
    		->add('email', 'email', array('label' => "Email:"))
    		->add('Edit', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {		// if request was sent
    		$form->handleRequest($request);
    		if ($form->isValid()) {					// and form was valid
    			try {
                    $this->editStudent($entity);
                    $request->getSession()->getFlashBag()->set('success', "Student #".$entity->getSid()." updated.");

                    // will not save filtering or sorting in display.
                    return $this->redirect($this->generateUrl('display_students'));
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
                }
    		}
    	}

    	return array('form' => $form->createView(), 'title' => "Edit Student");
    }

    /**
     * @Route("/", name="display_students")
     * @Route("/display")
     * @Template()
     */
    public function displayAction(Request $request) {
        $sort = $request->query->get('sort');
        $filter = $request->query->get('filter');

        if (!$sort) {
            $sort = 'sid';
        }
        $array = array();

        if ($filter) {
            list($sid, $fName, $lName, $email) = explode("-", $filter);
            $array = $this->generateArray($sid, $fName, $lName, $email);
        }

        try {
            $entities = $this->findStudent($array, $sort);
        } catch (BioException $e) {
            $entities = array();
        }

        return array('entities' => $entities, 'title' => "Display Students", 'sort' => $sort);
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

    // takes in an array containing desired field-value pairs and a field name to sort by
    // returns an array of all Students matching that description
    private function findStudent($array = array(), $sort = "sid") {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioStudentBundle:Student');

        $entities = $repo->findBy($array, array($sort => 'ASC'));
        if (count($entities) === 0) {
            throw new BioException("No students found.");
        } else {
            return $entities;
        }
    }

    // 
    private function addStudent($entity) {
        $em = $this->getDoctrine()->getManager();
        try {
            $em->persist($entity);
            $em->flush();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new BioException("That Student ID or email is already registered.");
        }
    }

    // takes in an array of entities
    // deletes all of them
    private function deleteStudent($entities) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioStudentBundle:Student');

        for ($i = 0; $i < count($entities); $i++) {
            $em->remove($entities[$i]);

        }
        $em->flush();
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
            while (strlen($sid) < 7) {
                $sid = "0".$sid;
            }
            $entity = new Student();
            $entity->setSid($sid);
            $entity->setEmail($email);
            list($lName, $fName) = explode(", ", $name);
            $entity->setFName(explode(" ", $fName)[0]);
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
