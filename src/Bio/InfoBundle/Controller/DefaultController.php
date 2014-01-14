<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/admin/{entityName}", requirements={
 *      "entityName" = "^announcement|hours|person|section|courseSection$",
 * })
 */
class DefaultController extends Controller {
	/**
     * @Route("/", name="view")
     */
    public function baseAction(Request $request, $entityName) {
        $lc = strtolower($entityName);
        $uc = ucfirst($entityName);
        $entityType = 'Bio\\InfoBundle\\Entity\\'.$uc;
        $formType = 'Bio\\InfoBundle\\Form\\'.$uc.'Type';

        $entity = new $entityType;
        $form = $this->createForm(new $formType, $entity)
            ->add('submit', 'submit');

        $db = new Database($this, 'BioInfoBundle:'.$uc);
        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($entity);
                $db->close();
                $flash->set('success', 'Successfully added.');
                return $this->redirect($this->generateUrl('view', array('entityName' => $entityName)));
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }



        if ($entityName === 'announcement') {
            $name = 'Announcements';
        } else if ($entityName === 'person') {
             $name = 'People';
        } else if ($entityName === 'hours') {
             $name = 'Hours';
        } else if ($entityName === 'courseSection') {
             $name = 'Course Sections';
        } else if ($entityName === 'section') {
             $name = 'Lab Sections';
        } else {
             $name = $uc;
        }

        if ($lc === 'hours') {
            $db = new Database($this, 'BioInfoBundle:Person');
            $entities = $db->find(array(), array(), false);
        } else {
            $entities = $entity->findSelf($db);
        }

        return $this->render('BioInfoBundle:'.$uc.':'.$lc.'.html.twig', 
                array(
                    'form' => $form->createView(),
                    'entities' => $entities,
                    'title' => 'Manage '.$name
                    )
                );
    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @ParamConverter("entity", class="BioInfoBundle:Base")
     */
    public function deleteAction(Request $request, $entityName, $entity = null) {
        $flash = $request->getSession()->getFlashBag();

        $lc = strtolower($entityName);
        $uc = ucfirst($entityName);
        $entityType = 'Bio\\InfoBundle\\Entity\\'.$uc;

        if ($entity && is_a($entity, $entityType)){
            $db = new Database($this, 'BioInfoBundle:Info');
            $db->delete($entity);

            try {
                $db->close();
                $flash->set('success', $full.' deleted.');
            } catch (BioException $e) {
                $flash->set('failure', 'Could not delete that object.');
            }
        } else {
            $flash->set('failure', 'Could not find that object.');
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view', array('entityName' => $entityName)));
        }
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @ParamConverter("entity", class="BioInfoBundle:Base")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function editAction(Request $request, $entityName, $entity = null) {
        $flash = $request->getSession()->getFlashBag();

        $lc = strtolower($entityName);
        $uc = ucfirst($entityName);
        $entityType = 'Bio\\InfoBundle\\Entity\\'.$uc;
        $formType = 'Bio\\InfoBundle\\Form\\'.$uc.'Type';
        
        if ($entity && is_a($entity, $entityType)) {

            $form = $this->createForm(new $formType, $entity)
                ->add('save', 'submit');

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);

                if($form->isValid()) {
                    $db = new Database($this, 'BioInfoBundle:'.$uc);
                    try {
                        $db->close();
                        $flash->set('success', 'Successfully edited.');
                        return $this->redirect($this->generateUrl("view", array('entityName' => $entityName)));
                    } catch (BioException $e) {
                        $flash->set('failure', 'Unable to save changes.');
                    }
                } else {
                    $flash->set('failure', 'Invalid form.');
                }
            }

            return array(
                'form' => $form->createView(),
                'title' => 'Edit '.$full
            );
        } else {
            $flash->set('failure', 'Could not find that object.');
        }

        return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
    }
}