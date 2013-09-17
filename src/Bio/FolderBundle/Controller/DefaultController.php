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
        $selected = 1;
        $private = false;
    	if ($request->query->get('id')) {
    		$selected = $request->query->get('id');
            if ($request->query->get('private')){
                $private = true;
            }
    	} 
    	$db = new Database($this, 'BioFolderBundle:Folder');

    	$sidebar = $db->findOne(array('name' => 'sidebar', 'parent' => null));
        $mainpage = $db->findOne(array('name' => 'mainpage', 'parent' => null));

        $folder = new Folder();
    	$form = $this->get('form.factory')->createNamedBuilder('tfolder', 'form', $folder)
    		->add('name', 'text', array('label' => 'Name:'))
            ->add('private', 'checkbox', array('label' => 'Private:', 'required' => false, 'attr' => $private?array('checked' => 'checked'):array()))
    		->add('add', 'submit')
    		->add('id', 'hidden', array('mapped' => false, 'data'=>$selected))
    		->getForm();

        $file = new File();
    	$form1 = $this->get('form.factory')->createNamedBuilder('tfile', 'form', $file)
    		->add('file', 'file', array('label' => false))
    		->add('name', 'text', array('label' => 'Name:'))
    		->add('id', 'hidden', array('mapped' => false, 'data' => $selected))
    		->add('upload', 'submit')
    		->getForm();

        $link = new Link();
        $form2 = $this->get('form.factory')->createNamedBuilder('tlink', 'form', $link)
            ->add('name', 'text', array('label' => 'Title:'))
            ->add('address', 'text', array('label' => 'URL:'))
            ->add('link', 'submit')
            ->add('id', 'hidden', array('mapped' => false, 'data'=>$selected))
            ->getForm();

        if ($request->getMethod() === "POST") {

            if ($request->request->has('tfolder')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $parent = $db->findOne(array('id' => $form->get('id')->getData()));
                    if (!$parent) {
                        $request->getSession()->getFlashBag()->set('failure', "Parent folder could not be found.");
                    } else {
                        $folder->setParent($parent);
                        if ($this->validate($folder, $form, 'name')) {
                            $parent->addFolder($folder);
                            $db->add($folder);
                            try {
                                $db->close();
                                $request->getSession()->getFlashBag()->set('success', "Folder \"".$folder->getName()."\" added.");
                                return $this->redirect($this->generateUrl('view_folders').'?id='.$selected.($private?'&private=1':''));
                            } catch (BioException $e) {
                                $request->getSession()->getFlashBag()->set('failure', "Folder could not be added.");
                                $db->delete($folder);
                            }
                        } else {
                            $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                }
            } else if ($request->request->has('tfile')) {
                $form1->handleRequest($request);
                if ($form1->isValid()) {
                    $parent = $db->findOne(array('id' => $form1->get('id')->getData()));
                    if (!$parent) {
                         $request->getSession()->getFlashBag()->set('failure', "Parent folder could not be found.");
                    } else {
                        $file->setParent($parent);
                        if ($this->validate($file, $form1, 'name')) {
                            $parent->addFile($file);
                            try {
                                $db->add($file);
                                $db->close("1");
                                $request->getSession()->getFlashBag()->set('success', "File \"".$file->getName()."\" uploaded.");
                                return $this->redirect($this->generateUrl('view_folders').'?id='.$selected.($private?'&private':''));
                            } catch (BioException $e) {
                                if ($e->getMessage() === '1') {
                                    $request->getSession()->getFlashBag()->set('failure', 'File could not be uploaded.');
                                } else {
                                    $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                                    if ($e->getMessage() === '3') {
                                        $form1->get('file')->addError(new FormError('No file uploaded.'));
                                    }
                                }

                                $parent->removeFile($file);
                            }
                        } else {
                            $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                }
            } else if ($request->request->has('tlink')) {
                $form2->handleRequest($request);
                if ($form2->isValid()) {
                    $parent = $db->findOne(array('id' => $form2->get('id')->getData()));
                    if (!$parent) {
                        $request->getSession()->getFlashBag()->set('failure', "Parent folder could not be found.");
                    } else {
                        $link->setParent($parent);
                        if ($this->validate($link, $form2, 'name')) {
                            $parent->addLink($link);
                            $db->add($link);
                            try {
                                $db->close();
                                $request->getSession()->getFlashBag()->set('success', "Link added.");
                                return $this->redirect($this->generateUrl('view_folders').'?id='.$selected.($private?'&private':''));
                            } catch (BioException $e) {
                                $request->getSession()->getFlashBag()->set('failure', 'Link could not be added.');
                                $parent->removeFile($file);
                            }
                        } else {
                            $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                }
            }
        }

        return array('root' => $sidebar, 'main' => $mainpage, 'selected' => $selected, 'folderForm'=>$form->createView(), 'fileForm' => $form1->createView(), 'linkForm' => $form2->createView(), 'title' => "View Folders");
    }

    private function validate($entity, $form, $field) {
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
     * @Route("/delete/{id}", name="delete_folder")
     * @ParamConverter("entity", class="BioFolderBundle:FileBase")
     */
    public function deleteAction(Request $request, $entity = null) {
        $type = $this->getType($entity);
		if(!$this->isRoot($entity)) {
            $db = new Database($this, 'BioFolderBundle:Folder'); 
			$db->delete($entity);
            try {
    			$db->close();
    			$request->getSession()->getFlashBag()->set('success', $type.' "'.$entity->getName().'" deleted.');
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', "Could not delete that ".$entity->getType());
            }

		} else {
			$request->getSession()->getFlashBag()->set('failure', "Could not find that file.");
		}

        return $this->redirect($this->generateUrl('view_folders'));
    }

    /**
     * @Route("/edit/{id}", name="edit_folder")
     * @Template()
     * @ParamConverter("entity", class="BioFolderBundle:FileBase")
     */
    public function editAction(Request $request, $entity = null) {
        if (!$this->isRoot($entity)) {
            $type = $this->getType($entity);
            $formBuilder = $this->createFormBuilder($entity);
            if ($type === "Folder") {
                $formBuilder->add('name', 'text', array('label' => 'Name:'))
                    ->add('private', 'checkbox', array('label' => 'Private:', 'required' => false, 'attr' => $entity->getPrivate()?array('checked' => 'checked'):array()))
                    ->add('save', 'submit');
            } else if ($type === "File") {
                $formBuilder->add('name', 'text', array('label' => 'Name:'))
                    ->add('save', 'submit');
            } else if ($type === "Link") {
                $formBuilder->add('name', 'text', array('label' => 'Title:'))
                    ->add('address', 'text', array('label' => 'URL:'))
                    ->add('save', 'submit');
            }
            $form = $formBuilder->getForm();

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);
                if($form->isValid()) {
                    $this->getDoctrine()->getManager()->flush();
                    $request->getSession()->getFlashBag()->set('success', $type.' saved.');
                    return $this->redirect($this->generateUrl('view_folders'));
                }
            }

            return array('form' => $form->createView(), 'title' => 'Edit '.$type);
        } else {
            $request->getSession()->getFlashBag()->set('failure', "Could not find that file.");
        }
    }

    private function isRoot($entity = null) {
        $hasName = method_exists($entity, 'getName');
        $isFolder = method_exists($entity, 'getFiles');
        $isParentless = $entity->getParent() === null;
        $name = $hasName?$entity->getName():'';

        return $isFolder && $hasName && $isParentless && ($name === 'sidebar' || $name === 'mainpage');
    }

    private function getType($entity = null) {
        return method_exists($entity, 'getPrivate')?"Folder":(method_exists($entity, 'getAddress')?"Link":"File");
    }

    /**
     * @Route("/students", name="student_folders")
     * @Template()
     */
    public function studentAction(Request $request) {
        $form = $this->createFormBuilder()
            // ->add('name', 'text', array('label' => 'Folder Name:'))
            ->add('parent', 'entity', array(
                'label' => 'Parent:',
                'class' => 'BioFolderBundle:Folder',
                'property' => 'name',
                'query_builder' => function(EntityRepository $repo) {
                    return $repo->createQueryBuilder('f')
                        ->where('f.parent IS NULL');
                }))
            ->add('confirmation', 'checkbox', array('label' => "Are you sure?"))
            ->add('create', 'submit')
            ->getForm();

        if($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {

            }
        }

        return array('form' => $form->createView(), 'title' => 'Create Student Folders');
    }

    /**
     * @Route("/clearall", name="clear_folders")
     * @Template()
     */
    public function clearAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('confirmation', 'checkbox', array('constraints' => new Assert\True(array('message' => "Please confirm."))))
            ->add('clear', 'submit', array('label' => 'Delete Folders'))
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db = new Database($this, 'BioFolderBundle:Folder');
                $root = $db->findOne(array('name' => "sidebar"));
                $db->deleteMany($root->getFolders()->toArray());
                $db->deleteMany($root->getFiles()->toArray());
                $db->deleteMany($root->getLinks()->toArray());

                $sidebar = $db->findOne(array('name' => "mainpage"));
                $db->deleteMany($sidebar->getFolders()->toArray());
                $db->deleteMany($sidebar->getFiles()->toArray());
                $db->deleteMany($sidebar->getLinks()->toArray());
                try {
                    $db->close();
                    $request->getSession()->getFlashBag()->set('success', 'All folders deleted.');
                } catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', 'Oops. Folders were not deleted.');
                }

                return $this->redirect($this->generateUrl('view_folders'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Delete All Folders');
    }
}
