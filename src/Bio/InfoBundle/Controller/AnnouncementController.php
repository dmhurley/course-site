<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Announcement;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/announcements")
 */
class AnnouncementController extends Controller
{
	/**
     * @Route("/", name="view_announcements")
     * @Template()
     */
    public function announcementsAction(Request $request) {
        $ann = new Announcement();
        $ann->setTimestamp(new \DateTime());
        $ann->setExpiration((new \DateTime())->modify('+1 day'));
        $form = $this->createFormBuilder($ann)
            ->add('timestamp', 'datetime', array('attr' => array('class' => 'datetime')))
            ->add('expiration', 'datetime', array('attr' => array('class' => 'datetime')))
            ->add('text', 'textarea')
            ->add('add', 'submit')
            ->getForm();


        $db = new Database($this, 'BioInfoBundle:Announcement');
        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $db->add($ann);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Announcement added.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Whoops.');
            }
        }

        $anns = $db->find(array(), array('expiration' => 'DESC'), false);
        return array('form' => $form->createView(),'anns' => $anns, 'title' => 'Edit Announcements');
    }

    /**
     * @Route("/delete", name="delete_announcement")
     */
    public function deleteAction(Request $request) {
        if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $db = new Database($this, 'BioInfoBundle:Announcement');

            $ann = $db->findOne(array('id'=>$id));
            if ($ann) {
                $db->delete($ann);
                $db->close();
                $request->getSession()->getFlashBag()->set('success', 'Announcement deleted.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that announcement.');
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('view_announcements'));
        }
    }

    /**
     * @Route("/edit", name="edit_announcement")
     * @Template()
     */
    public function editAction(Request $request) {
        $db = new Database($this, 'BioInfoBundle:Announcement');

        if ($request->getMethod() === "GET" && $request->query->get('id')){
            $id = $request->query->get('id');

            $ann = $db->findOne(array('id' => $id));
        } else {
            $ann = new Announcement();
        }

        $form = $this->createFormBuilder($ann)
            ->add('timestamp', 'datetime', array('attr' => array('class' => 'datetime')))
            ->add('expiration', 'datetime', array('attr' => array('class' => 'datetime')))
            ->add('text', 'textarea')
            ->add('id', 'hidden')
            ->add('edit', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if($form->isValid()) {
                $dbAnn = $db->findOne(array('id' => $ann->getId()));
                $dbAnn->setTimestamp($ann->getTimestamp())
                    ->setExpiration($ann->getExpiration())
                    ->setText($ann->getText());

                $db->close();

                return $this->redirect($this->generateUrl('view_announcements'));
            }
        }

        return array('form' => $form->createView(), 'title' => 'Edit Announcement');
    }
}