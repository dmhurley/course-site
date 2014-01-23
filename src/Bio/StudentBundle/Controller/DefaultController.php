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
use Bio\InfoBundle\Entity\CourseSection;
use Bio\StudentBundle\Form\StudentType;
use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Doctrine\DBAL\Types\Type as DBALType;

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
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioInfoBundle:Section');
        $findArray = $flash->peek('find');
        if ( isset($findArray['section'])) {
            $s = $db->find(array('id' => $findArray['section']), array(), false);
            if (!$s) {
                unset($findArray['section']);
            } else {
                $findArray['section'] = $s;
            }
        }
        $form = $this->createFormBuilder($request->getSession()->getFlashBag()->peek('find'))
            ->add('sid', 'text', array(
                'label' => 'Student ID:',
                'required' => false,
                'constraints' => new Assert\Regex("/[0-9]{7}/"),
                'attr' => array(
                        'pattern' => '[0-9]{7}',
                        'title' => "7 digit student ID"
                    )
                )
            )
            ->add('fName', 'text', array(
                'label' => 'First Name:',
                'required' => false
                )
            )
            ->add('lName', 'text', array(
                'label' => 'Last Name:',
                'required' => false
                )
            )
            ->add('section', 'entity', array(
                'label' => 'Section:',
                'required' => false,
                'class' => 'BioInfoBundle:Section',
                'property' => 'name',
                'data' => $flash->has('find')?$db->findOne(array('id' => $flash->peek('find'))):'',
                'empty_value' => '',
                'query_builder' => function($repo) {
                    return $repo->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                }
                )
            )
            ->add('email', 'text', array(
                'label' => 'Email:',
                'required' => false,
                'constraints' => new Assert\Email()
                )
            )
            ->add('find', 'submit')
            ->getForm();

        $result = array();
        if ($request->getMethod() === "POST" || $flash->has('find')) {
            $form->handleRequest($request);
            if ($request->getMethod() !== "POST") {
                $array = $flash->peek('find');
                $result = $this->findStudents($array);
            } else if ($form->isValid()) {
                $array = array_filter(array_slice($form->getData(), 0, 5));
                if (isset($array['section'])) {
                    $array['section'] = $array['section']->getId();
                }
                $flash->set('find', $array);
                $result = $this->findStudents($array);
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }

        return array(
            'form' => $form->createView(),
            'entities' => $result,
            'title' => 'Find Student'
            );
    }

    private function findStudents($array) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('s')
            ->from('BioStudentBundle:Student', 's');

        foreach(array_keys($array) as $i => $key) {
            if ($key === 'sid' || $key === 'email') {
                $array[$key] = DBALType::getType('privatestring')->encrypt($array[$key]);
            }

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
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function addAction(Request $request)
    {   
        $flash = $request->getSession()->getFlashBag();

    	$entity = new Student();
    	$form = $this->createForm(new StudentType(), $entity)
            ->add('submit', 'submit');

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {
    			try {
                    $db = new Database($this, 'BioStudentBundle:Student');

                    // create password based off last name
                    $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                    $entity->setPassword($encoder->encodePassword($entity->getLName(), $entity->getSalt()));

                    $db->add($entity);
                    $db->close();
                    $flash->set('success', 'Student added.');
                    return $this->redirect($this->generateUrl('add_student'));
                } catch (BioException $e) {
                    $error = new FormError("That student ID or email is already registered");
                    $form->get('sid')->addError($error);
                    $form->get('email')->addError($error);
                    $flash->set('failure', 'Invalid form.');
                }
    		} else {
    			$flash->set('failure', 'Invalid form.');
    		}
    	}
        return array(
            'form' => $form->createView(),
            'title' => "Add Student"
        );
    }

    /**
     * @Route("/delete/{id}", name="delete_student")
     */
    public function deleteAction(Request $request, Student $student = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($student !== null){
            $db = new Database($this, 'BioStudentBundle:Student');
            $db->delete($student);

            try {
                $db->close();
                $flash->set('success', "Student removed.");
            } catch (BioException $e){
                $flash->set('failure', "Could not delete student");
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
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function editAction(Request $request, Student $student = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($student === null) {
            $flash->set('failure', 'Could not find that student.');
            return $this->redirect($this->generateUrl('find_student'));
        }

    	$form = $this->createForm(new StudentType(), $student)
            ->add('save', 'submit');

    	if ($request->getMethod() === "POST") {		// if request was sent
    		$form->handleRequest($request);
    		if ($form->isValid()) {					// and form was valid
    			$db = new Database($this, 'BioStudentBundle:Student');
                try {
                    $db->close();
                    $flash->set('success', 'Student edited.');
                    return $this->redirect($this->generateUrl('find_student'));
                } catch (BioException $e) {
                    $form->get('email')->addError(new FormError("A student already has that email."));
                    $flash->set('failure', 'Invalid form.');
                }
    		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => "Edit Student");
    }

	/**
     * @Route("/upload", name="upload_student")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function uploadAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

    	$form = $this->createFormBuilder()
    		->add('file', 'file', array('label' => 'File:'))
    		->add('Upload', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		$data = $form->get('file')->getData();
    		if ($data !== null) {
    			$file = preg_split('/\n\r|\r\n|\n|\r/', file_get_contents($data), -1, PREG_SPLIT_NO_EMPTY);
    			try {
                    $count = $this->uploadStudentList($file);
                    $flash->set('success', "Uploaded $count students.");
                } catch (BioException $e) {
                    $form->get('file')->addError(new FormError($e->getMessage()));
                    $flash->set('failure', 'Upload error.');
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
        $studentCount = count($file);
        
        $headers = $studentCount > 0 ? array_flip(str_getcsv($file[0])) : [];

        for ($i = 1; $i < $studentCount; $i++) {

            $line = str_getcsv($file[$i]);
            $sid = $line[$headers['StudentNo']];
            $name = $line[$headers['Name']];
            $sectionName = isset($headers['LB Sect']) ? $line[$headers['LB Sect']] : 'AA';
            $email = isset($headers['Email']) ? $line[$headers['Email']] : '';

            if (!($sid && $name && $sectionName)) {
                throw new BioException("The file was badly formatted");
            }

            if ( !($section = $this->findObjectByFieldValue($sectionName, $dbSections, 'name')) && 
                 !($section = $this->findObjectByFieldValue($sectionName, $sections, 'name'))) {
                $section = new Section();
                $section->setName($sectionName)
                    ->setStart(new \DateTime('midnight'))
                    ->setEnd(new \DateTime('midnight'))
                    ->setDays([])
                    ->setBldg("HCK\tHitchcock Hall")
                    ->setRoom(0);
                $db->add($section);
            }
            if (!in_array($section, $sections)) {
                $sections[] = $section;
            }

            list($lName, $fName) = explode(",", $name);
            $lName = trim($lName);
            $fName = trim($fName);
            while (strlen($sid) < 7) {
                $sid = "0".$sid;
            }
            $entity = new Student();
            $firstAndMiddle = explode(' ', $fName);
            $entity->setSid($sid)
                ->setSection($section)
                ->setEmail($email)
                ->setFName($firstAndMiddle[0])
                ->setMName(count($firstAndMiddle) > 1 ? $firstAndMiddle[1] : '')
                ->setLName($lName)
                ->setPassword($encoder->encodePassword($lName, $entity->getSalt()));

            if (!in_array($sid, $sids) && !in_array($email, $emails)) {
                $sids[] = $sid;
                if ($email) {
                    $emails[] = $email;
                }
                $ents[] = $entity;
            } else {
                throw new BioException("The file contained duplicate Student IDs or emails.");
            }
        }
        foreach ($ents as $ent) {
            if ($dbEnt = $this->findObjectByFieldValue($ent->getSid(), $dbEnts, 'sid')) {
                $dbEnt->setLName($ent->getLName())
                    ->setMName($ent->getMName())
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

        $db = new Database($this, 'BioInfoBundle:CourseSection');
        $s = $db->find(array(), array(), false);

        $cSections = [];

        foreach($sections as $section) {
            if ( !($c = $this->findObjectByFieldValue(substr($section->getName(), 0, 1), $cSections, 'name')) &&
                 !($c = $this->findObjectByFieldValue(substr($section->getName(), 0, 1), $s, 'name'))) {
                $c = new CourseSection();
                $c->setName(substr($section->getName(), 0, 1))
                    ->setDays([])
                    ->setStartTime(new \DateTime('midnight'))
                    ->setEndTime(new \DateTime('midnight'))
                    ->setBldg("HCK\tHitchcock Hall")
                    ->setRoom("0");
                $db->add($c);
            }
            if (!in_array($c, $cSections)) {
                $cSections[] = $c;
            }
        }

        foreach ($s as $dbS) {
            if(!$this->findObjectByFieldValue($dbS->getName(), $cSections, 'name')) {
                $db->delete($dbS);
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
