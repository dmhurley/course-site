<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Bio\InfoBundle\Entity\Person;
use Bio\InfoBundle\Entity\Announcement;
use Bio\InfoBundle\Entity\Link;
use Bio\InfoBundle\Entity\Section;
use Bio\InfoBundle\Entity\Hours;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/admin/{entityName}", requirements={
 *      "entityName" = "^announcement|hours|link|person|section$",
 * })
 */
class DefaultController extends Controller {
	/**
     * @Route("/", name="view")
     */
    public function baseAction(Request $request, $entityName) {
        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        $type = 'Bio\InfoBundle\Entity\\'.$uc;

        $entity = new $type;
        if ($lc === 'announcement') {
            $entity->setTimestamp(new \DateTime());
            $entity->setExpiration((new \DateTime())->modify('+1 week'));
        } else if ($lc === 'link') {
            $entity->setAddress('http://');
        }
        $form = $entity->addToForm($this->createFormBuilder($entity))            
            ->add('add', 'submit')
            ->getForm();

        $formclone = clone $form;

        $db = new Database($this, 'BioInfoBundle:'.$uc);
        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($entity);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', $uc.' added.');
            } else {
                $formclone = $clone;
                $request->getSession()->getFlashBag()->set('failure', 'Whoops.');
            }
        }
        if ($lc === "announcement"){
            $entities = $db->find(array(), array('expiration' => 'DESC'), false);
        } else if ($lc === 'hours') {
            $db2 = new Database($this, 'BioInfoBundle:Person');
            $entities = $db2->find(array(), array(), false);
        } else {
            $entities = $db->find(array(), array(), false);
        }

        $plural = $uc[strlen($uc)-1]==='s'?$uc:$uc.'s';
        return $this->render('BioInfoBundle:'.$uc.':'.$lc.'.html.twig', 
                array('form' => $formclone->createView(), $lc.'s' => $entities, 'title' => 'Manage '.$plural));
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @ParamConverter("entity", class="BioInfoBundle:Base")
     */
    public function deleteAction(Request $request, $entityName, $entity = null) {
        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        if ($entity && is_a($entity, "Bio\InfoBundle\Entity\\".$uc)){
            $db = new Database($this, 'BioInfoBundle:Info');
            $db->delete($entity);

            try {
                $db->close();
                $request->getSession()->getFlashBag()->set('success', $uc.' deleted.');
            } catch (BioException $e) {
                $request->getSession()->getFlashBag()->set('failure', 'Could not delete that '.$lc.'.');
            }
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find that '.$lc.'.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
        }
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @ParamConverter("entity", class="BioInfoBundle:Base")
     */
    public function editAction(Request $request, $entityName, $entity = null) {
        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        
        if ($entity && is_a($entity, "Bio\InfoBundle\Entity\\".$uc)) {
            $form = $entity->addToForm($this->createFormBuilder($entity))
                ->add('id', 'hidden')
                ->add('edit', 'submit')
                ->getForm();

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);

                if($form->isValid()) {
                    $db = new Database($this, 'BioInfoBundle:'.$uc);
                    $dbEntity = $db->findOne(array('id' => $entity->getId()));
                    $dbEntity->setAll($entity);
                    try {
                        $db->close();
                        $request->getSession()->getFlashBag()->set('success', 'Edited that '.$lc.'.');
                    } catch (BioException $e) {
                        $request->getSession()->getFlashBag()->set('failure', 'Could not edit that '.$lc.'.');
                    }
                }
            }

            return $this->render('BioInfoBundle:'.$uc.':edit.html.twig', 
                    array('form' => $form->createView(), 'title' => 'Edit '.$uc));
        } else {
            $request->getSession()->getFlashBag()->set('failure', 'Could not find that '.$lc.'.');
        }

        return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
    }
}