<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bio\DataBundle\Objects\Database;
use Bio\UserBundle\Entity\User;
use Bio\InfoBundle\Entity\Announcement;
use Symfony\Component\Validator\Constraints as Assert;


class DefaultController extends Controller
{
    /**
     * @Route("/admin/", name="main_admin_page")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Admin Page');
    }

    /**
     * @Route("/", name="main_page")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $db = new Database($this, 'BioInfoBundle:Person');
        $instructors = $db->find(array('title' => 'instructor'), array('fName' => 'ASC', 'lName' => 'ASC'), false);
        $tas = $db->find(array('title' => 'ta'), array('fName' => 'ASC', 'lName' => 'ASC'), false);
        $coordinators = $db->find(array('title' => 'coordinator'), array('fName' => 'ASC', 'lName' => 'ASC'), false);

        $db = new Database($this, 'BioInfoBundle:Info');
        $info = $db->findOne(array());

        $db = new Database($this, 'BioInfoBundle:Section');
        $sections = $db->find(array(), array('name' => 'ASC'), false);

        $db = new Database($this, 'BioFolderBundle:Folder');
        $main = $db->findOne(array('name' => 'mainpage'));

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT a FROM BioInfoBundle:Announcement a WHERE a.expiration > :now AND a.timestamp < :now ORDER BY a.timestamp ASC'
        )->setParameter('now', new \DateTime());
        $anns = $query->getResult();

        return array('instructors' => $instructors, 'tas' => $tas, 'coordinators' => $coordinators, 'info' => $info,
            'sections' => $sections, 'anns' => $anns, 'main' => $main, 'title' => "Welcome");
    }

    /**
     * @Route("/folder/{id}", name="public_folder")
     * @Template()
     */
    public function folderAction(Request $request, $id) {
        $db = new Database($this, 'BioFolderBundle:Folder');
        $root = $db->findOne(array('id' => $id));

        if (!$root || $root->getPrivate()) {
            $request->getSession()->getFlashBag()->set('failure', 'Folder does not exist.');
            if ($request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            } else {
                return $this->redirect($this->generateUrl('public_folder', array('id' => 1)));
            }
        }

        return array('root' => $root, 'title' => $root->getName().' Folder');
    }
}
