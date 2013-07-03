<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Bio\InfoBundle\Entity\Person;
use Bio\InfoBundle\Entity\Announcement;
use Bio\InfoBundle\Entity\Link;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/{entityName}")
 */
class DefaultController extends Controller
{ // starting letter has to be higher than D
	/**
     * @Route("/", name="view")
     */
    public function baseAction(Request $request, $entityName) {
        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        $type = 'Bio\InfoBundle\Entity\\'.$uc;

        $entity = new $type;
        $form = $entity->addToForm($this->createFormBuilder($entity))            
            ->add('add', 'submit')
            ->getForm();


        $db = new Database($this, 'BioInfoBundle:'.$uc);
        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($entity);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', $uc.' added.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Whoops.');
            }
        }
        if ($lc === "announcement"){
            $entities = $db->find(array(), array('expiration' => 'DESC'), false);
        } else {
            $entities = $db->find(array(), array(), false);
        }
        return $this->render('BioInfoBundle:'.$uc.':'.$lc.'.html.twig', 
                array('form' => $form->createView(), $lc.'s' => $entities, 'title' => 'Edit '.$uc));
    }

    /**
     * @Route("/delete", name="delete")
     */
    public function deleteAction(Request $request, $entityName) {
        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        $type = 'Bio\InfoBundle\Entity\\'.$uc;

        if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $db = new Database($this, 'BioInfoBundle:'.$uc);

            $entity = $db->findOne(array('id'=>$id));
            if ($entity) {
                $db->delete($entity);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', $uc.' deleted.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that '.$lc.'.');
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
        }
    }

    /**
     * @Route("/edit", name="edit")
     */
    public function editAction(Request $request, $entityName) {
        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        $type = 'Bio\InfoBundle\Entity\\'.$uc;

        $db = new Database($this, 'BioInfoBundle:'.$uc);

        if ($request->getMethod() === "GET" && $request->query->get('id')){
            $id = $request->query->get('id');

            $entity = $db->findOne(array('id' => $id));
        } else {
            $entity = new $type;
        }

        $form = $entity->addToForm($this->createFormBuilder($entity))
            ->add('id', 'hidden')
            ->add('edit', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if($form->isValid()) {
                $dbEntity = $db->findOne(array('id' => $entity->getId()));
                $dbEntity->setAll($entity);
                $db->close();

                return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
            }
        }

        return $this->render('BioInfoBundle:'.$uc.':edit.html.twig', 
                array('form' => $form->createView(), 'title' => 'Edit Announcement'));
    }
}