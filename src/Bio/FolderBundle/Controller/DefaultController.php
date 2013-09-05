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

    	$root = $db->findOne(array('id' => 1));
        $folder = new Folder();
    	$form = $this->get('form.factory')->createNamedBuilder('form', 'form', $folder)
    		->add('name', 'text')
            ->add('private', 'checkbox', array('required' => false, 'attr' => $private?array('checked' => 'checked'):array()))
    		->add('add', 'submit')
    		->add('id', 'hidden', array('mapped' => false, 'data'=>$selected))
    		->getForm();

        $file = new File();
    	$form1 = $this->get('form.factory')->createNamedBuilder('global', 'form', $file)
    		->add('file', 'file')
    		->add('name', 'text')
    		->add('id', 'hidden', array('mapped' => false, 'data' => $selected))
    		->add('upload', 'submit')
    		->getForm();

        if ($request->getMethod() === "POST") {

            if ($request->request->has('form')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $parent = $db->findOne(array('id' => $form->get('id')->getData()));
                    if (!$parent) {
                        $request->getSession()->getFlashBag()->set('failure', "Parent folder could not be found.");
                    } else {
                        $folder->setParent($parent);
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
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                }
            } else if ($request->request->has('global')) {
                $form1->handleRequest($request);
                if ($form1->isValid()) {
                    $parent = $db->findOne(array('id' => $form1->get('id')->getData()));
                    if (!$parent) {
                         $request->getSession()->getFlashBag()->set('failure', "Parent folder could not be found.");
                    } else {
                        $parent->addFile($file);
                        $file->setParent($parent);
                        try {
                            $db->add($file);
                            $db->close("1");
                            $request->getSession()->getFlashBag()->set('success', "File \"".$file->getPath()."\" uploaded.");
                            return $this->redirect($this->generateUrl('view_folders').'?id='.$selected.($private?'&private':''));
                        } catch (BioException $e) {
                            if ($e->getMessage() === '1') {
                                $request->getSession()->getFlashBag()->set('failure', 'File could not be uploaded.');
                            } else {
                                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
                                if ($e->getMessage() === '2') {
                                    $form1->get('name')->addError(new FormError('A file with that name already exists.'));
                                } else if ($e->getMessage() === '3') {
                                    $form1->get('file')->addError(new FormError('No file uploaded.'));
                                }
                            }

                            $db->delete($file);
                            $parent->removeFile($file);
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                }
            }
        }

        return array('root' => $root, 'selected' => $selected, 'folderForm'=>$form->createView(), 'fileForm' => $form1->createView(), 'title' => "View Folders");
    }

    /**
     * @Route("/delete/{id}", name="delete_folder")
     * @ParamConverter("entity", class="BioFolderBundle:FileBase")
     */
    public function deleteAction(Request $request, $entity = null) {
		if($entity && (($type = method_exists($entity, 'getFiles')?"Folder":"File") === "File" || $entity->getId() !== 1)) {
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
                $root = $db->findOne(array('id' => 1));
                $db->deleteMany($root->getFolders()->toArray());
                $db->deleteMany($root->getFiles()->toArray());
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
