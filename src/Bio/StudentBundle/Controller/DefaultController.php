<?php

namespace Bio\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\StudentBundle\Entity\Student;
use Bio\InfoBundle\Entity\Section;
use Bio\StudentBundle\Form\StudentType;
use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Doctrine\DBAL\Types\Type;

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
     * @Template()
     */
    public function findAction(Request $request){
        $findArray = $request->getSession()->getFlashBag()->peek('find');
        if ( isset($findArray['section'])) {
            $db = new Database($this, 'BioInfoBundle:Section');
            $s = $db->find(array('id' => $findArray['section']), array(), false);
            if (!$s) {
                unset($findArray['section']);
            } else {
                $findArray['section'] = $s;
            }
        }
        $form = $this->createFormBuilder($request->getSession()->getFlashBag()->peek('find'))
            ->add('sid', 'text', array('label' => 'Student ID:', 'required' => false, 'attr' => array('disabled' => 'disabled')))
            ->add('fName', 'text', array('label' => 'First Name:', 'required' => false))
            ->add('lName', 'text', array('label' => 'Last Name:', 'required' => false))
            ->add('section', 'entity', array('label' => 'Section:', 'required' => false, 'class' => 'BioInfoBundle:Section', 'property' => 'name', 'empty_value' => '', 'query_builder' => function($repo) {return $repo->createQueryBuilder('s')->orderBy('s.name', 'ASC');}))
            ->add('email', 'text', array('label' => 'Email:','required' => false, 'attr' => array('disabled' => 'disabled')))
            ->add('find', 'submit')
            ->getForm();

        $result = array();
        if ($request->getMethod() === "POST" || $request->getSession()->getFlashBag()->has('find')) {
            $form->handleRequest($request);
            if ($request->getMethod() !== "POST") {
                $array = $request->getSession()->getFlashBag()->peek('find');
                $result = $this->findStudents($array);
            } else if ($form->isValid()) {
                $array = array_filter(array_slice($form->getData(), 0, 5));
                if (isset($array['section'])) {
                    $array['section'] = $array['section']->getId();
                }
                $request->getSession()->getFlashBag()->set('find', $array);
                $result = $this->findStudents($array);
            } else {
                var_dump($form->getData());
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
        }

        return array('form' => $form->createView(), 'entities' => $result, 'title' => 'Find Student');
    }

    private function findStudents($array) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('s')
            ->from('BioStudentBundle:Student', 's');

        foreach(array_keys($array) as $i => $key) {
            if (is_string($array[$key])) {
                $qb->andWhere('s.'.$key.' LIKE :value'.$i)
                    ->setParameter('value'.$i, $array[$key].'%');
            } else {
                $qb->andWhere('s.'.$key.' = :value'.$i)
                    ->setParameter('value'.$i, $array[$key]);
            }
        }
        if (count($array) > 0) {
            reset($array);
            $qb->orderBy('s.'.key($array), 'ASC');
        } else {
            $qb->orderBy('s.fName', 'ASC');
        }
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * @Route("/add", name="add_student")
     * @Template()
     */
    public function addAction(Request $request)
    {
    	$entity = new Student();
    	$form = $this->createForm(new StudentType(), $entity, array('label' => 'add'));

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {
    			try {
                    $db = new Database($this, 'BioStudentBundle:Student');
                    $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                    $entity->setPassword($encoder->encodePassword($entity->getLName(), $entity->getSalt()));
                    $db->add($entity);
                    $db->close("That Student ID or email is already registered.");
                    $request->getSession()->getFlashBag()->set('success', 'Student added.');
                    return $this->redirect($this->generateUrl('add_student'));
                } catch (BioException $e) {
                    $error = new FormError("That student ID or email is already registered");
                    $form->get('sid')->addError($error);
                    $form->get('email')->addError($error);
                    $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                }
    		} else {
    			$request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
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
                $request->getSession()->getFlashBag()->set('success', "Student removed.");
            } catch (BioException $e){
                $request->getSession()->getFlashBag()->set('failure', "Could not delete student");
            }
        }
    	
        if (!$request->headers->get('referer')){
            return $this->redirect($this->generateUrl('find_student'));
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
            return $this->redirect($this->generateUrl('find_student'));
        }

    	$form = $this->createForm(new StudentType(), $student, array('title' => 'edit', 'edit' => true));

    	if ($request->getMethod() === "POST") {		// if request was sent
    		$form->handleRequest($request);
    		if ($form->isValid()) {					// and form was valid
    			$db = new Database($this, 'BioStudentBundle:Student');
                try {
                    $db->close();
                    $request->getSession()->getFlashBag()->set('success', 'Student edited.');
                    return $this->redirect($this->generateUrl('find_student'));
                } catch (BioException $e) {
                    $form->get('email')->addError(new FormError("A student already has that email."));
                    $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                }
    		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
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
    		->add('file', 'file', array('label' => 'File:'))
    		->add('Upload', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		$data = $form->get('file')->getData();
    		if ($data !== null) {
    			$file = file($data, FILE_IGNORE_NEW_LINES);
    			try {
                    $count = $this->uploadStudentList($file);
                    $request->getSession()->getFlashBag()->set('success', "Uploaded $count students.");
                } catch (BioException $e) {
                    $form->get('file')->addError(new FormError($e->getMessage()));
                    $request->getSession()->getFlashBag()->set('failure', 'Upload error.');
                }
	    	}
    	}
    	return array("form" => $form->createView(), 'title' => "Upload Student List");
    }

    private function uploadStudentList($file) {
        $db = new Database($this, 'BioStudentBundle:Student');
        $dbEnts = $db->find(array(), array(), false);

        $db = new Database($this, 'BioInfoBundle:Section');
        $dbSections = $db->find(array(), array(), false);

        $encoder = $this->get('security.encoder_factory')->getEncoder(new Student());
       
        $sids = [];
        $emails = [];
        $ents = [];

        $sections = [];

        for ($i = 1; $i < count($file); $i++) {
            list($sid, $name, $sectionName, $credits, $gender, $class, $major, $email) = preg_split('/","|,"|",|"/', $file[$i], -1, PREG_SPLIT_NO_EMPTY);
            if (!($sid && $name && $sectionName && $credits && $gender && $class && $major && $email)) {
                throw new BioException("The file was badly formatted");
            }

            if (! ($section = $this->findObjectByFieldValue($sectionName, $dbSections, 'name')) && !($section = $this->findObjectByFieldValue($sectionName, $sections, 'name'))) {
                $section = new Section();
                $section->setName($sectionName)
                    ->setStart(new \DateTime('midnight'))
                    ->setEnd(new \DateTime('midnight'))
                    ->setDay('m')
                    ->setBldg("HCK\tHitchcock Hall")
                    ->setRoom(0);
                $db->add($section);
            }
            if (!in_array($section, $sections)) {
                $sections[] = $section;
            }

            list($lName, $fName) = explode(", ", $name);
            while (strlen($sid) < 7) {
                $sid = "0".$sid;
            }
            $entity = new Student();
            $entity->setSid($sid)
                ->setSection($section)
                ->setEmail($email)
                ->setFName(explode(' ', $fName)[0])
                ->setLName($lName)
                ->setPassword($encoder->encodePassword($lName, $entity->getSalt()));
            if (!in_array($sid, $sids) && !in_array($email, $emails)) {
                $sids[] = $sid;
                $emails[] = $email;
                $ents[] = $entity;
            } else {
                throw new BioException("The file contained duplicate Student IDs or emails.");
            }
        }
        foreach ($ents as $ent) {
            if ($dbEnt = $this->findObjectByFieldValue($ent->getSid(), $dbEnts, 'sid')) {
                $dbEnt->setLName($ent->getLName())
                    ->setFName($ent->getFName())
                    ->setSection($ent->getSection())
                    ->setEmail($ent->getEmail());
            } else {
                $db->add($ent);
            }
        }

        foreach ($dbEnts as $dbEnt) {
            if (!$this->findObjectByFieldValue($dbEnt->getSid(), $ents, 'sid')) {
                $db->delete($dbEnt);
            }
        }

        foreach($dbSections as $dbSection) {
            if (!$this->findObjectByFieldValue($dbSection->getName(), $sections, 'name')) {
                $db->delete($dbSection);
            }
        }

        $db->close();
        return count($ents);
    }

    // does array contain student, searching by Sid
    private function findObjectByFieldValue($needle, $haystack, $field) {
        $getter = 'get'.ucFirst($field);

        foreach ($haystack as $straw) {
            if (call_user_func_array(array($straw, $getter), array()) === $needle) {
                return $straw;
            } 
        }
        return null;
    }
}
