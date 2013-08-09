<?php

namespace Bio\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\StudentBundle\Entity\Student;
use Bio\StudentBundle\Form\StudentType;
use Bio\DataBundle\Exception\BioException;

use Bio\DataBundle\Objects\Database;

/**
 * @Route("/admin/student")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="students_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => "Students");
    }

    /**
     * @Route("/find", name="find_student")
     * @Template("BioStudentBundle:Default:delete.html.twig")
     */
    public function findAction(Request $request){
        $form = $this->createForm(new StudentType(), new Student(), array('label' => 'find', 'required' => true));

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
    	$form = $this->createForm(new StudentType(), $entity, array('label' => 'add'));

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
     * @Route("/delete/{id}", name="delete_student")
     */
    public function deleteAction(Request $request, Student $student = null) {
        if ($student !== null){
            $db = new Database($this, 'BioStudentBundle:Student');
            $db->delete($student);

            try {
                $db->close();
                $request->getSession()->getFlashBag()->set('success', "Student #".$student->getSid()." removed.");
            } catch (BioException $e){
                $request->getSession()->getFlashBag()->set('failure', "Could not delete student");
            }
        }
    	
        if (!$request->headers->get('referer')){
            return $this->redirect($this->generateUrl('display_students'));
        } else {
            return $this->redirect($request->headers->get('referer'));
        }
    }

    /**
     * @Route("/edit/{id}", name="edit_student")
     * @Template("BioStudentBundle:Default:add.html.twig")
     */
    public function editAction(Request $request, Student $student = null) {
        if ($student === null) {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find that student.');
            return $this->redirect($this->generateUrl('display_students'));
        }

    	$form = $this->createForm(new StudentType(), $student, array('title' => 'edit', 'edit' => true));

    	if ($request->getMethod() === "POST") {		// if request was sent
    		$form->handleRequest($request);
    		if ($form->isValid()) {					// and form was valid
    			$db = new Database($this, 'BioStudentBundle:Student');
                try {
                    $db->close();
                    $request->getSession()->getFlashBag()->set('success', 'Student edited.');
                    return $this->redirect($this->generateUrl('display_students'));
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', 'A student already has that email.');
                }
    		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => "Edit Student");
    }

    /**
     * @Route("/display", name="display_students")
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

    private function uploadStudentList($file) {
        $db = new Database($this, 'BioStudentBundle:Student');
        $dbEnts = $db->find(array(), array(), false);
       
        $sids = [];
        $emails = [];
        $ents = [];
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
                $ents[] = $entity;
            } else {
                throw new BioException("The file contained duplicate Student IDs or emails.");
            }
        }

        foreach ($ents as $ent) {
            if ($dbEnt = $this->getBySid($dbEnts, $ent)) {
                $dbEnt->setLName($ent->getLName())
                    ->setFName($ent->getFName())
                    ->setSection($ent->getSection())
                    ->setEmail($ent->getEmail());
            } else {
                $db->add($ent);
            }
        }

        foreach ($dbEnts as $dbEnt) {
            if (!$this->getBySid($ents, $dbEnt)) {
                $db->delete($dbEnt);
            }
        }

        $db->close();
    }

    // does array contain student, searching by Sid
    private function getBySid($array, $student) {
        foreach ($array as $a) {
            if ($a->getSid() === $student->getSid()) {
                return $a;
            }
        }
        return false;
    }
}
