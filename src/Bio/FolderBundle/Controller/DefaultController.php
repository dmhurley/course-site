<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityRepository;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Bio\FolderBundle\Entity\Folder;
use Bio\FolderBundle\Entity\File;
use Bio\FolderBundle\Entity\Link;
use Bio\FolderBundle\Form\FolderType;
use Bio\FolderBundle\Form\FileType;
use Bio\FolderBundle\Form\LinkType;

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
        $flash = $request->getSession()->getFlashBag();

        $form = $this->createFormBuilder()
            // ->add('name', 'text', array('label' => 'Folder Name:'))
            ->add('parent', 'entity', array(
                'label' => 'Parent:',
                'class' => 'BioFolderBundle:Folder',
                'property' => 'name',
                'query_builder' => function(EntityRepository $repo) {
                        return $repo->createQueryBuilder('f')
                            ->where('f.parent IS NULL');
                    }
                )
            )
            ->add('confirmation', 'checkbox', array('label' => "Are you sure?"))
            ->add('create', 'submit')
            ->getForm();

        if($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // does folder exist?
                $db = new Database($this, 'BioFolderBundle:Folder');

                $folder = $db->findOne(array(
                    'name' => 'Student Folders',
                    'parent' => $form->get('parent')->getData()
                    )
                );

                if (!$folder) {
                    $folder = new Folder();
                    $folder->setName('Student Folders')
                        ->setParent($form->get('parent')->getData())
                        ->setPrivate(false);
                    $db->add($folder);
                    $db->close();
                }

                $dbStudentFolders = $db->find(array('parent' => $folder), array(), false);
                $studentFolders = [];

                $db = new Database($this, 'BioStudentBundle:Student');
                $students = $db->find(array(), array(), false);

                foreach($students as $student) {
                    if ( !($f = $this->findObjectByFieldValue($student, $dbStudentFolders, 'student'))) {
                        $f = new Folder();
                        $f->setName($student->getFName().' '.$student->getLName().' ')
                            ->setStudent($student)
                            ->setParent($folder)
                            ->setPrivate(false);
                        $folder->addChild($f);
                        $db->add($f);
                    }
                    $studentFolders[] = $f;
                }

                foreach($dbStudentFolders as $f) {
                    if (!in_array($f, $studentFolders, true)) {
                        $db->delete($f);
                    }
                }
                try {
                    $db->close();
                    $flash->set('success', 'Created student folders.');
                } catch (BioException $e) {
                    $flash->set('failure', 'Could not create student folders.');
                }

                return $this->redirect($this->generateUrl('view_folders'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Create Student Folders');
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
