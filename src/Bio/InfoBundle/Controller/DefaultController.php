<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
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
        $flash = $request->getSession()->getFlashBag();
        $lc = strtolower($entityName);
        $uc = ucfirst($entityName);
        $full = implode(' ', preg_split('/((?:[A-Z])[a-z]+)/', $uc, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
        if ($entityName === 'person') {
            $name = 'People';
        } else {
            $name = $full.'s';
        }

        $entityType = 'Bio\\InfoBundle\\Entity\\'.$uc;
        $formType = 'Bio\\InfoBundle\\Form\\'.$uc.'Type';

        $entity = new $entityType;
        $form = $this->createForm(new $formType, $entity)
            ->add('submit', 'submit');

        $db = $this->get('bio.database')->createDatabase('BioInfoBundle:'.$uc);
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

        if ($lc === 'hours') {
            $db = $this->get('bio.database')->createDatabase('BioInfoBundle:Person');
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

        $uc = ucfirst($entityName);
        $entityType = 'Bio\\InfoBundle\\Entity\\'.$uc;
        $full = implode(' ', preg_split('/((?:[A-Z])[a-z]+)/', $uc, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));

        if ($entity && is_a($entity, $entityType)){
            $db = $this->get('bio.database')->createDatabase('BioInfoBundle:Info');
            $db->delete($entity);

            try {
                $db->close();
                $flash->set('success', $full . ' deleted.');
            } catch (BioException $e) {
                $flash->set('failure', 'Could not delete that ' . $full . ' .');
            }
        } else {
            $flash->set('failure', 'Could not find that ' . $full . '.');
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
        $full = implode(' ', preg_split('/((?:[A-Z])[a-z]+)/', $uc, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
        
        if ($entity && is_a($entity, $entityType)) {

            $form = $this->createForm(new $formType, $entity)
                ->add('save', 'submit');

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);

                if($form->isValid()) {
                    $db = $this->get('bio.database')->createDatabase('BioInfoBundle:'.$uc);
                    try {
                        $db->close();
                        $flash->set('success', $full.' edited.');
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
            $flash->set('failure', 'Could not find that ' . $full . '.');
        }

        return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
    }
}