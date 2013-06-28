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
     * @Route("/add")
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
        return array('form' => $form->createView());
    }

    /**
     * @Route("/delete")
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

    	return array('form' => $cloned->createView());
    }

    /**
     * @Route("/edit")
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

    	return array('form' => $form->createView());
    }
	/**
     * @Route("/upload")
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
    			$file = file($data);
    			$em = $this->getDoctrine()->getManager();
	    		for ($i = 1; $i < count($file); $i++) {
	    			$array = explode(",", $file[$i]);
	    			try {
	    				$entity = new Student();
	    				$entity->setSid(intval($array[0]));
	    				$entity->setEmail($array[7]);
	    				$entity->setFName($array[2]);
	    				$entity->setLName($array[1]);
	    				$em->persist($entity);
	    			} catch(\Doctrine\DBAL\DBALException $e) {

	    			}
	    		}
			$em->flush();
	    	}
    	}
    	return array("form" => $form->createView());
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
        
    }
}
