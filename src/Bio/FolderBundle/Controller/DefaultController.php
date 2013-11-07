<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Bio\FolderBundle\Entity\Folder;
use Bio\FolderBundle\Entity\File;
use Bio\FolderBundle\Entity\Link;
use Bio\FolderBundle\Form\FolderType;
use Bio\FolderBundle\Form\FileType;
use Bio\FolderBundle\Form\LinkType;
use Bio\FolderBundle\Form\StudentFolderType;

/**
 * @Route("/admin/folders")
 * @Template()
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="folders_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Folders');
    }

    /**
     * @Route("/manage", name="view_folders")
     * @Template()
     */
    public function indexAction(Request $request)
    {	 

        /****** ADD FOLDER FORM ******/
        $folder = new Folder();
    	$folderForm = $this->createForm(new FolderType(), $folder,
                array (
                    'action' => $this->generateUrl('create_entity', array(
                            'bundle' => 'folder',
                            'entityName' => 'folder'
                        )
                    )
                )
            )
            ->add('submit', 'submit');

        /****** ADD FILE FORM ******/
        $file = new File();
        $fileForm = $this->createForm(new FileType(), $file,
                array (
                    'action' => $this->generateUrl('create_entity', array(
                            'bundle' => 'folder',
                            'entityName' => 'file'
                        )
                    )
                )
            )
            ->add('submit', 'submit');

        /****** ADD LINK FORM ******/
        $link = new Link();
        $linkForm = $this->createForm(new LinkType(), $link,
                array (
                    'action' => $this->generateUrl('create_entity', array(
                            'bundle' => 'folder',
                            'entityName' => 'link'
                        )
                    )
                )
            )
            ->add('submit', 'submit');

        return array(
            'folderForm'=>$folderForm->createView(),
            'fileForm' => $fileForm->createView(),
            'linkForm' => $linkForm->createView(),
            'title' => "View Folders"
        );
    }

    /**
     * @Route("/students", name="student_folders")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function studentAction(Request $request) {
        $form = $this->createForm(new StudentFolderType(), null, array(
                'action' => $this->generateUrl('generate_student_folders')
            )
        )
            ->add('create', 'submit');

        return array('form' => $form->createView(), 'title' => 'Create Student Folders');
    }

    /**
     * @Route("/students/generate", name="generate_student_folders")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function generateStudentFoldersAction(Request $request) {
        $form = $this->createForm(new StudentFolderType(), null)
            ->add('create', 'submit');

        $form->handleRequest($request);
        if ($form->isValid()) {
            $db = new Database($this, 'BioFolderBundle:Folder');
            $studentFolder = $db->findOne(array(
                    'name' => 'Student Folders',
                    'parent' => $form->get('parent')->getData()
                )
            );

            // make sure there is a student folder
            if (!$studentFolder) {
                $studentFolder = new Folder();
                $studentFolder->setName('Student Folders')
                    ->setParent($form->get('parent')->getData())
                    ->setPrivate(false);
                $db->add($studentFolder);
                $db->close();
            }

            // make sure there are alpha folders
            foreach(range('A', 'Z') as $character) {
                $children = $db->find(array('parent' => $studentFolder), array('name' => 'ASC'), false);
                if (!$this->findObjectByFieldValue($character, $children, 'name')) {
                    $folder = new Folder();
                    $folder->setName($character)
                        ->setParent($studentFolder)
                        ->setPrivate(false);
                    $db->add($folder);
                }
            }
            $db->close();

            $children = $db->find(array('parent' => $studentFolder), array('name' => 'ASC'), false);
            if (count($children) > 26) {
                return array(
                    'error' => 'Delete all non capital character folders.'
                );
            }

            $folders = array_combine(range('A', 'Z'), $children);

            $students = (new Database($this, 'BioStudentBundle:Student'))->find(array(), array(), false);
            $studentsWithFolders = [];
            foreach($students as $student) {
                $character = strtoupper(substr($student->getLName(), 0, 1));
                if ( !($f = $this->findObjectByFieldValue($student, $folders[$character]->getChildren()->toArray(), 'student'))) {
                    $f = new Folder();
                    $f->setName($student->getLName().', '.$student->getFName())
                        ->setStudent($student)
                        ->setParent($folders[$character])
                        ->setPrivate(false);
                    $db->add($f);
                    $studentsWithFolders[] = $f;
                }
            }
            $db->close();

            foreach($folders as $folder) {
                $alphaFolders = $folder->getChildren()->toArray();
                foreach($alphaFolders as $studentFolder) {
                    if (!in_array($studentFolder, $studentsWithFolders, true)) {
                        $db->delete($studentFolder);
                    }
                }
            }
            $db->close();

            return array(
                'entities' => [$studentFolder] //student folder
            );
        } else {
            return array(
                'form' => $form->createView(),
                'error' => 'Invalid form.'
            );
        }
    }

    private function findObjectByFieldValue($needle, $haystack, $field) {
        $getter = 'get'.ucFirst($field);

        foreach ($haystack as $straw) {
            if (call_user_func_array(array($straw, $getter), array()) === $needle) {
                return $straw;
            } 
        }
        return null;
    }

    /**
     * @Route("/clearall", name="clear_folders")
     * @Template()
     */
    public function clearAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $form = $this->createFormBuilder()
            ->add('confirmation', 'checkbox', array(
                'constraints' => new Assert\True(
                    array('message' => "Please confirm.")
                    )
                )
            )
            ->add('clear', 'submit', array('label' => 'Delete Folders'))
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db = new Database($this, 'BioFolderBundle:Folder');
                $root = $db->findOne(array('name' => "sidebar"));
                $db->deleteMany($root->getChildren()->toArray());

                $sidebar = $db->findOne(array('name' => "mainpage"));
                $db->deleteMany($sidebar->getChildren()->toArray());
                try {
                    $db->close();
                    $flash->set('success', 'All folders deleted.');
                } catch (BioException $e) {
                    $flash->set('failure', 'Oops. Folders were not deleted.');
                }

                return $this->redirect($this->generateUrl('view_folders'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Delete All Folders');
    }
}
