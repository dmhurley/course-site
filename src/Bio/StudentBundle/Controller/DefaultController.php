<?php

namespace Bio\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\StudentBundle\Entity\Student;
use Bio\DataBundle\Exception\BioException;

use Bio\DataBundle\Objects\Database;

/**
 * @Route("/admin/student")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/find", name="find_student")
     * @Template("BioStudentBundle:Default:delete.html.twig")
     */
    public function findAction(Request $request){
        $form = $this->createFormBuilder(new Student())
            ->add('sid', 'text', array('label' => "Student ID:", 'required' => false, 'attr' => array('pattern' => '[0-9]{7}', 'title' => '7 digit student ID')))
            ->add('fName', 'text', array('label' => "First Name:", 'required' => false))
            ->add('lName', 'text', array('label' => "Last Name:", 'required' => false))
            ->add('section', 'text', array('label' => "Section:", 'required' => false))
            ->add('email', 'email', array('label' => "Email:", 'required' => false))
            ->add('Find', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $values = $request->request->get('form');
            $array = array_filter(array_slice($values, 0, 5));
            try {
                $db = new Database($this, 'BioStudentBundle:Student');
                $entities = $db->find($array, array('sid' => 'ASC'));
                $request->getSession()->getFlashBag()->set('success', 'Students found!');

                $get = "?filter=".implode(array_slice($values, 0, 5), '-');

                return $this->redirect($this->generateUrl('display_students').$get);
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
    		->add('sid', 'text', array('label' => "Student ID:", 'attr' => array('pattern' => '[0-9]{7}', 'title' => '7 digit student ID')))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
            ->add('section', 'text', array('label' => "Section:"))
    		->add('email', 'email', array('label' => "Email:"))
    		->add('Add', 'submit')
    		->getForm();

    	$cloned = clone $form;

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {
    			try {
                    $db = new Database($this, 'BioStudentBundle:Student');
                    $db->add($entity);
                    $db->close("That Student ID or email is already registered.");
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
                $db = new Database($this, 'BioStudentBundle:Student');
                $db->deleteBy(array('sid' => $sid));
                $db->close();
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
                $db = new Database($this, 'BioStudentBundle:Student');
                $entity = $db->findOne(array('sid' => $request->query->get('sid')));
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
            }
        }

    	$form = $this->createFormBuilder($entity)
    		->add('sid', 'text', array('label' => "Student ID:", 'read_only'=> true))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
            ->add('section', 'text', array('label' => "Section:"))
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
            $array = explode("-", $filter);
            $array = array_combine(array('sid', 'fName', 'lName', 'section', 'email'), $array);
            $array = array_filter($array);
        }

        try {
            $db = new Database($this, 'BioStudentBundle:Student');
            $entities = $db->find($array, array($sort => 'ASC'));
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
    		->add('file', 'file', array('label' => 'File:'))
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
                $dbEntity->setSection($entity->getSection());
                $dbEntity->setEmail($entity->getEmail());
                // for more changes
                $em->flush();
            } catch (\Doctrine\DBAL\DBALException $e) {
                throw new BioException('A student already has that email.');
            }
        }
    }

    private function uploadStudentList($file) {
        $db = new Database($this, 'BioStudentBundle:Student');
        $entities = $db->truncate();
       
        $sids = [];
        $emails = [];

        for ($i = 1; $i < count($file); $i++) {
            list($sid, $name, $section, $credits, $gender, $class, $major, $email) = preg_split('/","|,"|",|"/', $file[$i], -1, PREG_SPLIT_NO_EMPTY);
            list($lName, $fName) = explode(", ", $name);
            while (strlen($sid) < 7) {
                $sid = "0".$sid;
            }
            $entity = new Student();
            $entity->setSid($sid)
                ->setSection($section)
                ->setEmail($email)
                ->setFName(explode(" ", $fName)[0])
                ->setLName($lName);
            if (!in_array($sid, $sids) && !in_array($email, $emails)) {
                $sids[] = $sid;
                $emails[] = $email;
                $db->add($entity);
            } else {
                $db->clear();
                for ($j = 0; $j < count($entities); $j++) {
                    $em->persist($entities[$j]);
                }
                $db->close();
                throw new BioException("The file contained duplicate Student IDs or emails.");
            }
        }
        $db->close();
    }
}
