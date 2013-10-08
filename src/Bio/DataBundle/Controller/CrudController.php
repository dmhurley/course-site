<?php

namespace Bio\DataBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Crud controller.
 *
 * @Route("/crud/{bundle}/{entityName}")
 */
class CrudController extends Controller
{

    /**
     * GET ALL all entities.
     *
     * @Route("/all.json", name="get_entities")
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function allAction(Request $request, $bundle, $entityName)
    {   
        $repo = $this->getRepository($bundle, $entityName);
        $entities = $repo->findAll();
        return array(
            'entities' => $entities,
        );
    }
    /**
     * CREATE a new entity.
     *
     * @Route("/create.json", name="create_entity")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function createAction(Request $request, $bundle, $entityName)
    {   
        $entity = $this->createEntity($bundle, $entityName);
        $form = $this->createForm($this->createFormType($bundle, $entityName), $entity)
            ->add('add', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return array(
                'entities' => array($entity)
            );
        }
        return array(
            'form' => $form,
            'error' => 'Invalid form.'
            );        
    }

    /**
     * GET an entity.
     *
     * @Route("/get/{id}.json", name="get_entity")
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function getAction(Request $request, $bundle, $entityName, $id)
    {
        $entity = $this->getEntity($bundle, $entityName, $id);

        if (!$entity) {
            return array('error' => 'Entity not found.');
        }

        return array(
            'entities' => [$entity]
        );
    }

    /**
     * EDIT an existing entity.
     *
     * @Route("/edit/{id}.json", name="edit_entity")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function editAction(Request $request, $bundle, $entityName, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getEntity($bundle, $entityName, $id);

        $form = $this->createForm($this->createFormType($bundle, $entityName), $entity)
            ->add('add', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return array (
                'entities' => [$entity]
            );
        } else {
            return array(
                'error' => 'Invalid form.',
                'form' => $form
            );
        }
    }
    /**
     * DELETE an entity.
     *
     * @Route("/delete/{id}.json", name="delete_entity")
     * @Template("BioDataBundle:Crud:response.json.twig")
     */
    public function deleteAction(Request $request, $bundle, $entityName, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getEntity($bundle, $entityName, $id);

        if (!$entity) {
            return array('error' => 'Entity not found.');
        }

        $em->remove($entity);
        $em->flush();

        return array('entities' => [$entity]);
    }

    private function getRepository($bundle, $entityName) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('Bio'.ucfirst($bundle).'Bundle:'.ucfirst($entityName));
        return $repo;
    }

    private function getEntity($bundle, $entityName, $id) {
        return $this->getRepository($bundle, $entityName)->find($id);
    }

    private function createFormType($bundle, $entityName) {
        $formType = 'Bio\\'.ucfirst($bundle).'Bundle\Form\\'.ucfirst($entityName).'Type';
        return new $formType;
    }

    private function createEntity($bundle, $entityName) {
        $type = 'Bio\\'.ucfirst($bundle).'Bundle\Entity\\'.ucfirst($entityName);
        return new $type;
    }
}
