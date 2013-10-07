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
 * @Route("/crud/{bundle}/{entity}")
 */
class CrudController extends Controller
{

    /**
     * GET ALL all entities.
     *
     * @Route("/all.json", name="get_entities")
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function allAction(Request $request, $bundle, $entity)
    {   
        $repo = $this->getRepository($bundle, $entity);
        $entities = $repo->findAll();
        return array(
            'entities' => $entities,
        );
    }
    /**
     * CREATE a new entity.
     *
     * @Route("/create.json", name="create_entity")
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function createAction(Request $request, $bundle, $entity)
    {   
        $type = 'Bio\\'.ucfirst($bundle).'Bundle\Entity\\'.ucfirst($entity);

        $object = new $type;
        $form = $this->createForm($this->getFormType($bundle, $entity), $object,
                array(
                    'action' => $this->generateUrl('create_entity', array(
                        'bundle' => 'exam',
                        'entity' => 'exam'
                        )
                    )
                )
            )
            ->add('add', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($object);
            $em->flush();

            return array(
                'entities' => array($object),
                'message' => 'YAY'
            );
        }
        return array('error' => json_encode($form->getErrors()));        
    }

    /**
     * GET an entity.
     *
     * @Route("/get/{id}.json", name="get_entity")
     * @Template("BioDataBundle:Crud:all.json.twig")
     */
    public function getAction(Request $request, $bundle, $entity, $id)
    {
        $entity = $this->getEntity($bundle, $entity, $id);

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
     * @Route("/{id}.json", name="exam_exam_update")
     * @Template("BioExamBundle:Exam:edit.html.twig")
     */
    public function editAction(Request $request, $bundle, $entity, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getRepository($bundle, $entity, $id);

        // $editForm->handleRequest($request);

        if (/*$editForm->isValid()*/false) {
            $em->flush();

            return $this->redirect($this->generateUrl('exam_exam_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
        );
    }
    /**
     * DELETE an entity.
     *
     * @Route("/delete/{id}.json", name="delete_entity")
     * @Template("BioExamBundle:Exam:response.json.twig")
     */
    public function deleteAction(Request $request, $bundle, $entity, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getEntity($bundle, $entity, $id);

        if (!$entity) {
            return array('error' => 'Entity not found.');
        }

        $em->remove($entity);
        $em->flush();

        return array();
    }

    private function getRepository($bundle, $entity) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('Bio'.ucfirst($bundle).'Bundle:'.ucfirst($entity));
        return $repo;
    }

    private function getEntity($bundle, $entity, $id) {
        return $this->getRepository($bundle, $entity)->find($id);
    }

    private function getFormType($bundle, $entity) {
        $formType = 'Bio\\'.ucfirst($bundle).'Bundle\Form\\'.ucfirst($entity).'Type';
        return new $formType;
    }
}
