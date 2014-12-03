<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Bio\FolderBundle\Entity\Folder;
use Bio\FolderBundle\Entity\File;
use Bio\FolderBundle\Entity\Link;
use Bio\FolderBundle\Entity\FileBase;
use Bio\FolderBundle\Form\FolderType;
use Bio\FolderBundle\Form\FileType;
use Bio\FolderBundle\Form\LinkType;
use Bio\FolderBundle\Form\StudentFolderType;

/**
 * @Route("/admin/folders")
 * @Template()
 */
class AdminController extends Controller
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
        $flash = $request->getSession()->getFlashBag();
        $folderRepo = $this->getDoctrine()
            ->getManager()
            ->getRepository('BioFolderBundle:Folder');

        // parse the query string for state
        $parent = null; // defaults to mainpage

    	if ($request->query->get('id')) {
            $parent = $folderRepo->findOneBy(array('id' => $request->query->get('id')));
    	} else {
            $parent = $folderRepo->getMainpageFolder();
        }

        // create forms
        $forms = array(
            'tfolder' => $this->get('form.factory')
                ->createNamedBuilder('tfolder', new FolderType(), new Folder())
                ->add('add', 'submit')
                ->getForm(),

            'tfile' => $this->get('form.factory')
                ->createNamedBuilder('tfile', new FileType(), new File())
                ->add('add', 'submit')
                ->getForm(),

            'tlink' => $this->get('form.factory')
                ->createNamedBuilder('tlink', new LinkType(), new Link())
                ->add('add', 'submit')
                ->getForm()
        );

        if ($request->getMethod() === "POST") {

            $form = null;

            // the the POSTed form
            if ($request->request->has('tfolder')) {
                $form = $forms['tfolder'];
            } else if ($request->request->has('tfile')) {
                $form = $forms['tfile'];
            } else if ($request->request->has('tlink')) {
                $form = $forms['tlink'];
            }

            // check for validity
            $form->handleRequest($request);
            $entity = $form->getData();
            if ($form->isValid() && $this->validate($entity, $form, 'name')) {

                // try to save entity to database
                $result = $this->getDoctrine()
                               ->getManager()
                               ->getRepository('BioFolderBundle:FileBase')
                               ->create($entity, $parent);

                // respond based on result
                if ($result['success']) {
                    $flash->set('success', $result['message']);
                    return $this->redirect(
                        $this->generateUrl('view_folders', array(
                            'id' => $parent ? $parent->getId() : undefined
                        ))
                    );
                } else {
                    $flash->set('failure', $result['message']);
                }

            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }

        return array(
            'root' => $folderRepo->getSidebarFolder(),
            'main' => $folderRepo->getMainpageFolder(),
            'parent' => $parent,
            'folderForm'=> $forms['tfolder']->createView(),
            'fileForm' => $forms['tfile']->createView(),
            'linkForm' => $forms['tlink']->createView(),
            'title' => "View Folders"
        );
    }

    /**
     * @Route("/delete/{id}", name="delete_folder")
     * @ParamConverter("entity", class="BioFolderBundle:FileBase")
     */
    public function deleteAction(Request $request, FileBase $entity = null) {
        $flash = $request->getSession()->getFlashBag();

        $result = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('BioFolderBundle:FileBase')
                     ->delete($entity);

        $flash->set(
            $result['success'] ? 'success' : 'failure',
            $result['message']
        );

        return $this->redirect($this->generateUrl('view_folders'));
    }

    /**
     * @Route("/edit/{id}", name="edit_folder")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     * @ParamConverter("entity", class="BioFolderBundle:FileBase")
     */
    public function editAction(Request $request, FileBase $entity = null) {
        $flash = $request->getSession()->getFlashBag();
        $destination = $this->redirect($this->generateUrl('view_folders'));
        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('BioFolderBundle:FileBase');
        $type = $repo->getType($entity);

        // no entity or unknown type
        if (!$entity || !$type) {
            $flash->set('failure', 'Could not find that file.');
            return $destination;
        }

        $form = null;

        //
        // Make right kind of form
        //

        if ($type === "Folder") {
            $form = $this->get('form.factory')
                ->createBuilder(new FolderType(), $entity)
                ->add('save', 'submit')
                ->getForm();
        } else if ($type === "Link") {
            $form = $this->get('form.factory')
                ->createBuilder(new LinkType(), $entity)
                ->add('save', 'submit')
                ->getForm();
        } else if ($type === "File") {
            $form = $this->get('form.factory')
                ->createBuilder('form', $entity)
                ->add('name', 'text', array(
                    'label' => 'Name:'
                ))
                ->add('save', 'submit')
                ->getForm();
        }

        //
        // Handle form submission
        //

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $flash->set('success', $type.' saved.');
                return $destination;
            }
        }

        return array('form' => $form->createView(), 'title' => 'Edit '.$type);
    }

    /**
     * @Route("/students", name="student_folders")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function studentAction(Request $request) {
        // TODO refactor this method...
        $flash = $request->getSession()->getFlashBag();

        $form = $this->createForm(new StudentFolderType(), null)
            ->add('create', 'submit');

        if($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // does folder exist?
                $db = new Database($this, 'BioFolderBundle:Folder');

                $folder = $db->findOne(array(
                    'name' => 'Student Folders',
                    'parent' => $form->get('parent')->getData()
                ));

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
                $result = $this->getDoctrine()
                               ->getManager()
                               ->getRepository('BioFolderBundle:FileBase')
                               ->clearAll();

                $flash->set(
                    $result['success'] ? 'success' : 'failure',
                    $result['message']
                );

                return $this->redirect($this->generateUrl('view_folders'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Delete All Folders');
    }

    /**
     * Searches the haystack by calling the field getter on each for the needle
     * @param {String} needle
     * @param {Collection} $haystack
     * @param {String} field - turns into get'Field' function
     * @return {Object|null}
     */
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
     * Validates the entire form and puts the first error on the given field
     * @param  {Entity} $entity
     * @param  {Form} Form    $form
     * @param  {String} $field
     * @return {Boolean}
     */
    private function validate($entity, Form $form, $field) {
        $validator = $this->get('validator');
        $errors = $validator->validate($entity);
        if (count($errors) > 0) {
            $form->get($field)->addError(new FormError($errors[0]->getMessage()));
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns true if the given entity is a root folder
     * @param {FileBase} $entity = null
     * @return {Boolean}
     */
    private function isRoot(FileBase $entity = null) {
        $hasName = method_exists($entity, 'getName');
        $isFolder = method_exists($entity, 'getFiles');
        $isParentless = $entity && $entity->getParent() === null;
        $name = $hasName ? $entity->getName() : '';

        return $isFolder && $hasName && $isParentless && ($name === 'sidebar' || $name === 'mainpage');
    }
}
