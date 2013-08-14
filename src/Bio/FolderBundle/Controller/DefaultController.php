<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


use Symfony\Component\HttpFoundation\Request;

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
                        } catch (BioException $e) {
                            $request->getSession()->getFlashBag()->set('failure', "Folder could not be added.");
                            $db->delete($folder);
                        }
                    }
                } else {
                    $request->getSession()->getFlashBag()->set('failure', "Invalid form.");
                }
            }

            if ($request->request->has('global')) {
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
                            $db->close("File could not be uploaded.");
                            $request->getSession()->getFlashBag()->set('success', "File \"".$file->getPath()."\" uploaded.");
                        } catch (BioException $e) {
                             $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
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
    public function deleteAction(Request $request, $entity) {
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
			$request->getSession()->getFlashBag()->set('failure', "Could not find that ".$type);
		}

        return $this->redirect($this->generateUrl('view_folders'));
    }

    /**
     * @Route("/clearall", name="clear_folders")
     * @Template()
     */
    public function clearAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('confirmation', 'checkbox')
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
                    $request->getSession()->getFlashBag()->set('failure', 'Oops. Folders were not cleared.');
                }

                return $this->redirect($this->generateUrl('view_folders'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Delete All Folders');
    }
}
