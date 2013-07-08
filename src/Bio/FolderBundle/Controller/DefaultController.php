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
     * @Route("/folders")
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
    	$selectedFolder = $db->findOne(array('id'=> $selected));

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
     * @Template()
     */
    public function structureAction($root, $selected) {
    	return array('root' => $root, 'selected' => $selected);
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
    			$request->getSession()->getFlashBag()->set('success', $type.' deleted.');
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
    	if ($request->getMethod() === "POST"){
	    	$form = $this->createFormBuilder()
	    		->setAction($this->generateUrl('add_folder'))
	    		->add('name', 'text')
	    		->add('add', 'submit')
	    		->add('id', 'hidden', array('mapped' => false))
	    		->getForm();

	    	$form->handleRequest($request);

	    	$db = new Database($this, 'BioFolderBundle:Folder');

	    	$folder = new Folder();
	    	$folder->setName($form->get('name')->getData());

	    	$parent = $db->findOne(array('id' => $form->get('id')->getData()));
	   		$folder->setParent($parent);
	    	$parent->addFolder($folder);
	    	$db->add($folder);
	    	$db->close();
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
            $parent->addFile($file);
            $file->setParent($parent);
            $db->add($file);
            $db->close();
            
	    }

	    return $this->redirect($this->generateUrl('view_folders').'?id='.$form->get('id')->getData());
    }
}
