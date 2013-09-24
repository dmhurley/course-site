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
        $flash = $request->getSession()->getFlashBag();

        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        $type = 'Bio\InfoBundle\Entity\\'.$uc;
        $full = [];
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $entityName, $full);
        $full = ucfirst(implode(' ', $full[0]));

        $entity = new $type;
        if ($lc === 'announcement') {
            $entity->setTimestamp(new \DateTime());
            $entity->setExpiration((new \DateTime())->modify('+1 week'));
        }
        $form = $entity->addToForm($this->createFormBuilder($entity))            
            ->add('add', 'submit')
            ->getForm();

        $db = new Database($this, 'BioInfoBundle:'.$uc);
        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($entity);
                $db->close();
                $flash->set('success', $full.' added.');
                return $this->redirect($this->generateUrl('view', array('entityName' => $entityName)));
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }
        if ($lc === 'hours') {
            $db = new Database($this, 'BioInfoBundle:Person');
            $entities = $db->find(array(), array(), false);
        } else {
            $entities = $entity->findSelf($db);
        }

        $plural = $uc[strlen($uc)-1]==='s'?$full:$full.'s';
        return $this->render('BioInfoBundle:'.$uc.':'.$lc.'.html.twig', 
                array(
                    'form' => $form->createView(), $lc.'s' => $entities,
                    'title' => 'Manage '.$plural 
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
        $lc = strtolower($entityName);
        $full = [];
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $entityName, $full);
        $full = ucfirst(implode(' ', $full[0]));

        if ($entity && is_a($entity, "Bio\InfoBundle\Entity\\".$uc)){
            $db = new Database($this, 'BioInfoBundle:Info');
            $db->delete($entity);

            try {
                $db->close();
                $flash->set('success', $full.' deleted.');
            } catch (BioException $e) {
                $flash->set('failure', 'Could not delete that '.$full.'.');
            }
        } else {
            $flash->set('failure', 'Could not find that '.$full.'.');
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

        $uc = ucfirst($entityName);
        $lc = strtolower($entityName);
        $full = [];
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $entityName, $full);
        $full = ucfirst(implode(' ', $full[0]));
        
        if ($entity && is_a($entity, "Bio\InfoBundle\Entity\\".$uc)) {
            $form = $entity->addToForm($this->createFormBuilder($entity))
                ->add('id', 'hidden')
                ->add('save', 'submit')
                ->getForm();

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);

                if($form->isValid()) {
                    $db = new Database($this, 'BioInfoBundle:'.$uc);
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

            return array('form' => $form->createView(), 'title' => 'Edit '.$full);
        } else {
            $flash->set('failure', 'Could not find that '.$full.'.');
        }

        return $this->redirect($this->generateUrl('view', array('entityName' => $lc)));
    }
}