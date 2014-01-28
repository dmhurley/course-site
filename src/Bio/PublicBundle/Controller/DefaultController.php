<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Bio\FolderBundle\Entity\File;


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
        $flash = $request->getSession()->getFlashBag();

        /**************** GET PEOPLE ***************/
        $db = $this->get('bio.database')->createDatabase('BioInfoBundle:Person');
        $instructors = $db->find(
            array('title' => 'instructor'),
            array('fName' => 'ASC', 'lName' => 'ASC'),
            false
            );
        $tas = $db->find(
            array('title' => 'ta'),
            array('fName' => 'ASC', 'lName' => 'ASC'),
            false
            );
        $coordinators = $db->find(
            array('title' => 'coordinator'),
            array('fName' => 'ASC', 'lName' => 'ASC'),
            false
            );

        /**************** GET INFO ***************/
        $db = $this->get('bio.database')->createDatabase('BioInfoBundle:Info');
        $info = $db->findOne(array());

        /**************** GET SECTIONS ***************/
        $db = $this->get('bio.database')->createDatabase('BioInfoBundle:Section');
        $lSections = $db->find(
            array(),
            array('name' => 'ASC'),
            false
            );

        $db = $this->get('bio.database')->createDatabase('BioInfoBundle:CourseSection');
        $cSections = $db->find(
            array(),
            array('name' => 'ASC'),
            false
        );

        /**************** GET DIRECTORIES ***************/
        $db = $this->get('bio.database')->createDatabase('BioFolderBundle:Folder');
        $root = $db->findOne(array('name' => 'mainpage', 'parent' => null));

        $folders = $db->find(
            array('parent' => $root),
            array('name' => 'ASC'),
            false
            );

        $db = $this->get('bio.database')->createDatabase('BioFolderBundle:File');
        $files = $db->find(
            array('parent' => $root),
            array('name' => 'ASC'),
            false
        );

        $db = $this->get('bio.database')->createDatabase('BioFolderBundle:Link');
        $links = $db->find(
            array('parent' => $root),
            array('name' => 'ASC'),
            false
        );

        /**************** GET GET ANNOUNCEMENTS ***************/
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT a 
             FROM BioInfoBundle:Announcement a 
             WHERE a.expiration > :now 
             AND a.timestamp < :now 
             ORDER BY a.timestamp ASC'
        )->setParameter('now', new \DateTime());
        $anns = $query->getResult();
        
        /**************** CREATE FORM ***************/
        $db = $this->get('bio.database')->createDatabase('BioPublicBundle:PublicGlobal');
        $global = $db->findOne(array());
        $choiceArray = [
            'info',
            'cSection',
            'anns',
            'folders',
            'files',
            'links',
            'instructors',
            'tas',
            'coordinators',
            'lSections'
            ];
        $form = $this->createFormBuilder($global)
            ->add('showing', 'choice', array(
                'choices' => $choiceArray,
                'expanded' => true,
                'multiple' => true
                )
            )
            ->add('description', 'textarea', array(
                'label' => false,
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('save', 'submit')
            ->getForm();

        if ($request->getMethod() === "POST" && $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
                $flash->set('success', 'Changes saved.');
            } else {
                $flash->set('failure', 'Form Invalid.');
            }
        }

        echo $info->getCourseNumber();


        return array(
            'instructors' => $instructors,
            'tas' => $tas,
            'coordinators' => $coordinators,
            'info' => $info,
            'lSections' => $lSections,
            'cSections' => $cSections,
            'anns' => $anns,
            'form' => $form->createView(),
            'folders' => $folders,
            'files' => $files,
            'links' => $links,
            'title' => "Welcome"
            );
    }

    /**
     * @Route("/folder/{id}", name="public_folder")
     * @Template()
     */
    public function folderAction(Request $request, $id) {
        $flash = $request->getSession()->getFlashBag();

        $db = $this->get('bio.database')->createDatabase('BioFolderBundle:Folder');
        $root = $db->findOne(array('id' => $id));

        if (!$root || $root->getPrivate()) {
            $flash->set('failure', 'Folder does not exist.');
            if ($request->headers->get('referer')) {
                return $this->redirect($request->headers->get('referer'));
            } else {
                return $this->redirect($this->generateUrl('public_folder', array('id' => 1)));
            }
        }

        $form = null;
        $student = $this->get('security.context')->getToken()->getUser();
        if ($root->getStudent() === $student && $student) {
            $file = new File();
            $form = $this->createFormBuilder($file)
                ->add('file', 'file', array(
                    'label' => 'File:', 
                        'constraints' => new Assert\File(
                            array(
                                'maxSize' => '32M'
                            )
                        )
                    )
                )
                ->add('name', 'text', array('label' => 'Name:'))
                ->add('upload', 'submit')
                ->getForm();

            if ($request->getMethod() === "POST") {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $db->add($file);
                    $file->setParent($root);

                    $root->addChild($file);
                    $db->close();
                }
            }
            $form = $form->createView();
        }

        return array(
            'root' => $root,
            'form' => $form,
            'title' => $root->getName().' Folder'
            );
    }

    /**
     * @Route("/folder/{id}/delete/{id2}", name="delete_public_file")
     */
    public function deleteFolderAction(Request $request, $id, $id2) {
        $flash = $request->getSession()->getFlashBag();

        $db = $this->get('bio.database')->createDatabase('BioFolderBundle:File');
        $root = $db->findOne(array('id' => $id2));
        $student = $this->get('security.context')->getToken()->getUser();

        if (!$root || $root->getParent()->getStudent() !== $student) {
            $flash->set('failure', 'Folder does not exist.');
        } else {
            $db->delete($root);
            $db->close();
        }
        if ($request->headers->get('referer')) {
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('main_page'));
        }
    }
}
