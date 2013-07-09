<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     * @Route("/", name="view_folders")
     * @Template()
     */
    public function indexAction(Request $request)
    {	
    	if ($request->query->get('id')) {
    		$selected = $request->query->get('id');
    	} else {
    		$selected = 1;
    	}
    	$db = new Database($this, 'BioFolderBundle:Folder');

    	$root = $db->findOne(array('id' => 1));

    	$form = $this->createFormBuilder()
    		->setAction($this->generateUrl('add_folder'))
    		->add('name', 'text')
    		->add('add', 'submit')
    		->add('id', 'hidden', array('mapped' => false, 'data'=>$selected))
    		->getForm();

    	$form1 = $this->createFormBuilder()
    		->setAction($this->generateUrl('add_file'))
    		->add('file', 'file')
    		->add('name', 'text')
    		->add('id', 'hidden', array('mapped' => false, 'data' => $selected))
    		->add('upload', 'submit')
    		->getForm();

        return array('root' => $root, 'selected' => $selected, 'folderForm'=>$form->createView(), 'fileForm' => $form1->createView(), 'title' => "View Folders");
    }

    /**
     * @Route("/delete", name="delete_folder")
     */
    public function deleteAction(Request $request) {
    	if ($request->getMethod() === 'GET' && $request->query->get('id') && $request->query->get('type')){
    		$id = $request->query->get('id');
    		$type = ucFirst($request->query->get('type'));

    		$db = new Database($this, 'BioFolderBundle:'.$type);

    		$entity = $db->findOne(array('id' => $id));
    		if($entity) {
    			$db->delete($entity);
    			$db->close();
    			$request->getSession()->getFlashBag()->set('success', $type.' "'.$entity->getName().'" deleted.');
    		} else {
    			$request->getSession()->getFlashBag()->set('failure', "Could not find that ".$type);
    		}
    	}

        return $this->redirect($this->generateUrl('view_folders'));
    }

    /**
     * @Route("/addfolder", name="add_folder")
     */
    public function addFolderAction(Request $request) {
        $folder = new Folder();
    	if ($request->getMethod() === "POST"){
	    	$form = $this->createFormBuilder($folder)
	    		->add('name', 'text')
	    		->add('id', 'hidden', array('mapped' => false))
	    		->getForm();

	    	$form->handleRequest($request);

	    	$db = new Database($this, 'BioFolderBundle:Folder');
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
                }
            }
	    }

	    return $this->redirect($this->generateUrl('view_folders').'?id='.$form->get('id')->getData());
    }

	/**
     * @Route("/addfile", name="add_file")
     */
    public function addFileAction(Request $request) {
    	if ($request->getMethod() === "POST"){
            $file = new File();

	    	$form = $this->createFormBuilder($file)
    		->add('file', 'file')
    		->add('name', 'text')
    		->add('id', 'hidden', array('mapped' => false))
    		->getForm();

	    	$form->handleRequest($request);

            $db = new Database($this, 'BioFolderBundle:Folder');

            $parent = $db->findOne(array('id' => $form->get('id')->getData()));
            if (!$parent) {
                 $request->getSession()->getFlashBag()->set('failure', "Parent folder could not be found.");
            } else {
                $parent->addFile($file);
                $file->setParent($parent);
                $db->add($file);
                try {
                    $db->close();
                    $request->getSession()->getFlashBag()->set('success', "File \"".$file->getPath()."\" uploaded.");
                } catch (BioException $e) {
                     $request->getSession()->getFlashBag()->set('failure', "File could not be uploaded.");
                }
            }
            
	    }

	    return $this->redirect($this->generateUrl('view_folders').'?id='.$form->get('id')->getData());
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
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'All folders deleted.');

                return $this->redirect($this->generateUrl('view_folders'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Delete All Folders');
    }
}
