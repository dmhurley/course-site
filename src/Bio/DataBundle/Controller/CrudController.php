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
            ->add('submit', 'submit');

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
            'form' => $form->createView(),
            'error' => 'Invalid form.'
            );        
    }

    /**
     * CREATE a new USER
     * 
     * @Route("/create_user.json", name="create_user")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function createUserAction(Request $request, $bundle, $entityName) {
        if ($entityName !== 'student' && $entityName !== 'user') {
            return $this->create404Response();
        } else {
            $entity = $this->createEntity($bundle, $entityName);
            $form = $this->createForm($this->createFormType($bundle, $entityName), $entity)
                ->add('submit', 'submit');

            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($entityName === 'student') {
                    $password = $entity->getLName();
                } else {
                    $password = $entity->getPassword();
                }

                $encoder = $this->get('security.encoder_factory')->getEncoder($entity);
                $entity->setPassword($encoder->encodePassword($password, $entity->getSalt()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                return array(
                    'entities' => array($entity)
                );
            }

            return array(
                'form' => $form->createView(),
                'error' => 'Invalid form.'
            );
        }
    }

    /**
     * GET an entity.
     *
     * @Route("/get/{id}.json", name="get_entity")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function getAction(Request $request, $bundle, $entityName, $id)
    {
        $entity = $this->getEntity($bundle, $entityName, $id);

        $form = $this->createForm($this->createFormType($bundle, $entityName), $entity);

        if (!$entity) {
            return array('error' => 'Entity not found.');
        }

        return array(
            'entities' => [$entity],
            'form' => $form->createView()
        );
    }

    /**
     * FIND entities with basic parameters
     *
     * @Route("/find.json", name="find_entity")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function findAction(Request $request, $bundle, $entityName) {
        $findValues = [];

        if ($request->getMethod() === "POST") {
            $entity = $this->createEntity($bundle, $entityName);
            $form = $this->createForm($this->createFormType($bundle, $entityName), $entity)
                ->add('submit', 'submit');
            $form->handleRequest($request);

            foreach($form->all() as $child) {
                if ($child->getData() !== null) {
                    $findValues[$child->getName()] = $child->getData();
                }
            }
        } else {
            $findValues = $request->request;
        }
        $repo = $this->getRepository($bundle, $entityName);
        $qb = $repo->createQueryBuilder('e');
        $index = 0;
        foreach($findValues as $key => $value) {
            if ($value) {
                $qb->andWhere('e.'.$key.' = :value'.$index)
                    ->setParameter('value'.$index, $value);
            } else {
                $qb->andWhere('e.'.$key.' IS NULL');
            }
            $index++;
        }
        $result = $qb->getQuery()->getResult();
        return array(
            'entities' => $result
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
            ->add('submit', 'submit');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return array (
                'entities' => [$entity],
            );
        } else {
            return array(
                'error' => 'Invalid form.',
                'form' => $form->createView()
            );
        }
    }
    /**
     * GET or EDIT(post request) a global entity
     *
     * @Route("/global.json", name="global_entity")
     * @Template("BioDataBundle:Crud:full.json.twig")
     */
    public function globalAction(Request $request, $bundle, $entityName) {
        $em = $this->getDoctrine()->getManager();
        $global = $this->getRepository($bundle, $entityName)->findOneBy(array());

        $form = $this->createForm($this->createFormType($bundle, $entityName), $global)
            ->add('save', 'submit');

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
            } else {
                return array(
                    'form' => $form->createView(),
                    'error' => 'Invalid form.'
                );
            }
        }

        return array(
            'form' => $form->createView()
        );
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
            return array(
                'entities' => [$this->createEntity($bundle, $entityName)],
                'message' => 'Entity not found.'
                );
        }

        if ($bundle === 'folder' && $entity->getParent() === null) {
            return array('error' => 'Folder cannot be deleted.');
        } else if ($bundle === 'user' && in_array('ROLE_SETUP', $entity->getRoles())) {
            return array('error' => 'User cannot be deleted.');
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
