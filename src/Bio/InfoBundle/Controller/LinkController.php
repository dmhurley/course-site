<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Link;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/links")
 */
class LinkController extends Controller
{
	/**
     * @Route("/", name="view_links")
     * @Template()
     */
    public function linksAction(Request $request) {
        $link = new Link();
        $form = $this->createFormBuilder($link)
            ->add('title', 'text')
            ->add('address', 'text')
            ->add('location', 'choice', array('choices' => array('sidebar' => 'Sidebar', 'content' => 'Main page')))
            ->add('add', 'submit')
            ->getForm();


        $db = new Database($this, 'BioInfoBundle:Link');
        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($link);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Link added.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Whoops.');
            }
        }

        $links = $db->find(array(), array(), false);
        return array('form' => $form->createView(),'links' => $links, 'title' => 'Edit Links');
    }

    /**
     * @Route("/delete", name="delete_link")
     */
    public function deleteAction(Request $request) {
        if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $db = new Database($this, 'BioInfoBundle:Link');

            $link = $db->findOne(array('id'=>$id));
            if ($link) {
                $db->delete($link);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Announcement deleted.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that link.');
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_links'));
        }
    }

    /**
     * @Route("/edit", name="edit_link")
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