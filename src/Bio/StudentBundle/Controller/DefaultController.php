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
use Bio\DataBundle\Type\Type;

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
     * @Route("/student", name="manage_students")
     * @Template("BioStudentBundle:Default:find.html.twig")
     */
    public function addAction(Request $request)
    {   

    	$entity = new Student();
    	$form = $this->createForm(new StudentType(), $entity,
            array(
                'action' => $this->generateUrl('create_user', array(
                        'bundle' => 'student',
                        'entityName' => 'student'
                    )
                )
            )
        );
                   
        return array(
            'form' => $form->createView(),
            'title' => "Manage Students"
        );
    }

   /**
     * @Route("/upload", name="upload_student")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function uploadStudentListAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('file', 'file', array('label' => 'File:'))
            ->add('Upload', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            return $this->uploadAction($request);
        }

        return array(
            'form' => $form->createView(),
            'title' => 'Upload Student List'
        );
    }

	/**
     * @Route("/../../crud/student/student/upload.json", name="upload_student_list")
     */
    public function uploadAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('file', 'file', array('label' => 'File:'))
    		->add('Upload', 'submit')
    		->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->get('file')->getData();
            if ($data !== null) {
                $file = preg_split('/\n\r|\r\n|\n|\r/', file_get_contents($data), -1, PREG_SPLIT_NO_EMPTY);
                try {
                    $count = $this->uploadStudentList($file);
                    return $this->render('BioDataBundle:Crud:full.json.twig', array(
                        'success' => true,
                        'message' => 'Uploaded '.$count.' students.'
                    ));
                } catch (BioException $e) {
                    $form->get('file')->addError(new FormError($e->getMessage()));
                    return $this->render('BioDataBundle:Crud:full.json.twig', array(
                        'form' => $form->createView(),
                        'error' => 'Upload error.'
                    ));
                }
            }

        }

         return $this->render('BioDataBundle:Crud:full.json.twig', array(
            'form' => $form->createView(),
            'error' => 'Invalid form.'
        ));
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
            try {
                list($sid, $name, $sectionName, $credits, $gender, $class, $major, $email) = str_getcsv($file[$i]);
            } catch (\Exception $e) {
                 throw new BioException("The file was badly formatted");
            }
            if (!($sid && $name && $sectionName &&
                  $credits && $gender && $class && $major)) {
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
            $entity->setSid($sid)
                ->setSection($section)
                ->setEmail($email)
                ->setFName(explode(' ', $fName)[0])
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
