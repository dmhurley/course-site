<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Person;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/people")
 */
class PersonController extends Controller
{
	/**
     * @Route("/", name="view_people")
     * @Template()
     */
    public function peopleAction(Request $request) {
        $person = new Person();
        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
        $form = $this->createFormBuilder($person)
            ->add('fName', 'text')
            ->add('lName', 'text')
            ->add('email', 'email')
            ->add('bldg', 'choice', array('choices' => array_combine($array, $array), 'validation_groups' => false))
            ->add('room', 'text')
            ->add('title', 'choice', array('choices' => array('instructor' => 'Instructor', 'ta' => 'TA', 'coordinator' => 'Coordinator')))
            ->add('add', 'submit')
            ->getForm();


        $db = new Database($this, 'BioInfoBundle:Person');
        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($person);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Link added.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Whoops.');
            }
        }

        $people = $db->find(array(), array(), false);
        return array('form' => $form->createView(),'people' => $people, 'title' => 'Edit People');
    }

    /**
     * @Route("/delete", name="delete_person")
     */
    public function deleteAction(Request $request) {
        if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $db = new Database($this, 'BioInfoBundle:Person');

            $person = $db->findOne(array('id'=>$id));
            if ($person) {
                $db->delete($person);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Person deleted.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that person.');
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_people'));
        }
    }

    /**
     * @Route("/edit", name="edit_person")
     * @Template()
     */
    public function editAction(Request $request) {
        $db = new Database($this, 'BioInfoBundle:Link');

        if ($request->getMethod() === "GET" && $request->query->get('id')){
            $id = $request->query->get('id');

            $link = $db->findOne(array('id' => $id));
        } else {
            $link = new Link();
        }

        $form = $this->createFormBuilder($link)
            ->add('title', 'text')
            ->add('address', 'text')
            ->add('location', 'choice', array('choices' => array('sidebar' => 'Sidebar', 'mainpage' => 'Main Page')))
            ->add('id', 'hidden')
            ->add('edit', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if($form->isValid()) {
                $dbAnn = $db->findOne(array('id' => $link->getId()));
                $dbAnn->setTitle($link->getTitle())
                    ->setAddress($link->getAddress())
                    ->setLocation($link->getLocation());

                $db->close();

                return $this->redirect($this->generateUrl('view_links'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Edit Announcement');
    }
}