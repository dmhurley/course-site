<?php

namespace Bio\ExamBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Bio\ExamBundle\Entity\Exam;
use Bio\ExamBundle\Form\ExamType;

/**
 * Exam controller.
 *
 * @Route("/exam/exam")
 */
class ExamController extends Controller
{

    /**
     * Lists all Exam entities.
     *
     * @Route("/get", name="exam_exam")
     * @Template("BioExamBundle:Exam:index.json.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BioExamBundle:Exam')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Exam entity.
     *
     * @Route("/create", name="exam_exam_create")
     * @Template("BioExamBundle:Exam:index.json.twig")
     */
    public function createAction(Request $request)
    {   
        $entity = new Exam();
        $form = $this->get('form.factory')->createNamedBuilder('form', 'form', $entity)
            ->add('title', 'text', array('label'=>'Exam Name:'))
            ->add('section', 'text', array(
                'label'=>'Section:',
                'required' => false,
                'empty_data' => '',
                'attr' => array(
                    'pattern' => '(\A\Z)|(^[A-Z][A-Z0-9]?$)',
                    'title' => 'One or two letter capitalized section name.'
                    )
                )
            )
            ->add('tDate', 'date',        array('label' => 'Test Date:'))
            ->add('tStart', 'time',       array('label'=>'Test Start:'))
            ->add('tEnd', 'time',         array('label'=>'Test End:'))
            ->add('tDuration', 'integer', array('label'=>'Test Length (m):'))
            ->add('gDate', 'date',        array('label' => 'Grading Date:'))
            ->add('gStart', 'time',       array('label'=>'Grading Start:'))
            ->add('gEnd', 'time',         array('label'=>'Grading End:'))
            ->add('gDuration', 'integer', array('label'=>'Grade Length (m):'))
            ->add('add', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return array(
                'entities' => array($entity),
            );
        } else {
        }

        return array('entities' => []);
        
    }

    /**
    * Creates a form to create a Exam entity.
    *
    * @param Exam $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Exam $entity)
    {
        $form = $this->createForm(new ExamType(), $entity, array(
            'action' => $this->generateUrl('exam_exam_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Exam entity.
     *
     * @Route("/new", name="exam_exam_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Exam();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Exam entity.
     *
     * @Route("/{id}", name="exam_exam_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BioExamBundle:Exam')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Exam entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Exam entity.
     *
     * @Route("/{id}/edit", name="exam_exam_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BioExamBundle:Exam')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Exam entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Exam entity.
    *
    * @param Exam $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Exam $entity)
    {
        $form = $this->createForm(new ExamType(), $entity, array(
            'action' => $this->generateUrl('exam_exam_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Exam entity.
     *
     * @Route("/{id}", name="exam_exam_update")
     * @Method("PUT")
     * @Template("BioExamBundle:Exam:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BioExamBundle:Exam')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Exam entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('exam_exam_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Exam entity.
     *
     * @Route("/delete/{id}", name="exam_exam_delete")
     * @Template("BioExamBundle:Exam:response.json.twig")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BioExamBundle:Exam')->find($id);

        if (!$entity) {
            return array('error' => 'Unable to find entity.');
        }

        $em->remove($entity);
        $em->flush();

        return array();
    }
}
