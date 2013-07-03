<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Info;
use Bio\InfoBundle\Entity\Announcement;

/**
 * @Route("/course")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/edit/course", name="edit_info")
     * @Template()
     */
    public function indexAction(Request $request) {
    	$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('BioInfoBundle:Info');
		$info = $repo->findOneBy(array());

		if (!$info) {
			$info = new Info();
			$em->persist($info);
		}

    	$form = $this->createFormBuilder($info)
    		->add('courseNumber', 'text')
    		->add('title', 'text')
    		->add('qtr', 'choice', array('choices' => array(
    					'au' => 'Autumn',
    					'wi' => 'Winter',
    					'sp' => 'Spring',
    					'su' => 'Summer'
    				)))
    		->add('year', 'integer')
    		->add('days', 'choice', array('choices' => array(
    					'm' => 'Monday',
    					'tu' => 'Tuesday',
    					'w' => 'Wednsday',
    					'th' => 'Thursday',
    					'f' => 'Friday',
    					'sa' => 'Saturday'
    				), 'multiple' => true))
    		->add('startTime', 'time')
    		->add('endTime', 'time')
    		->add('bldg', 'choice', array('choices' => file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES)))
    		->add('room', 'text')
    		->add('email', 'email')
    		->add('edit', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$data = $form->getData();
    			$info->setCourseNumber($data->getCourseNumber());
    			$info->setTitle($data->getTitle());
    			$info->setQtr($data->getQtr());
    			$info->setYear($data->getYear());
    			$info->setDays($data->getDays());
    			$info->setStartTime($data->getStartTime());
    			$info->setEndTime($data->getEndTime());
    			$info->setBldg($data->getBldg());
    			$info->setRoom($data->getRoom());
    			$info->setEmail($data->getEmail());

    			try {
    				$em->flush();
    			} catch (\Doctrine\DBAL\DBALException $e) {

    			}
    		}
    	}

        return array('form' => $form->createView(), 'title' => "Edit Course Information");
    }

    /**
     * @Route("/announcements", name="view_announcements")
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

        if ($request->getMethod() === "POST") {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($ann);
                $em->flush();
                $request->getSession()->getFlashBag()->set('success', 'Announcement added.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Whoops.');
            }
        }

        $anns = $this->getAnnouncements();
        return array('form' => $form->createView(),'anns' => $anns, 'title' => 'Edit Announcements');
    }

    /**
     * @Route("/delete", name="delete_announcement")
     */
    public function deleteAction(Request $request) {
        if ($request->getMethod() === "GET" && $request->query->get('id')) {
            $id = $request->query->get('id');

            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('BioInfoBundle:Announcement');

            $ann = $repo->findOneById($id);
            if ($repo) {
                $em->remove($ann);
                $em->flush();
                $request->getSession()->getFlashBag()->set('success', 'Announcement deleted.');
            } else {
                $request->getSession()->getFlashBag()->set('failure', 'Could not find that announcement.');
            }
        }

        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } else {
            return $this->redirect($this->generateUrl('edit_announcements'));
        }
    }

    /**
     * @Route("/edit/announcement", name="edit_announcement")
     * @Template()
     */
    public function editAction(Request $request) {
        $ann = new Announcement();
        if ($request->getMethod() === "GET" && $request->query->get('id')){
            $id = $request->query->get('id');
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('BioInfoBundle:Announcement');

            $anns = $repo->findBy(array('id' => $id));
            if (count($anns) === 1) {
                $ann = $anns[0];
            }
            echo '..'.$ann->getText().'..';
        }

        $form = $this->createFormBuilder($ann)
            ->add('timestamp', 'datetime', array('attr' => array('class' => 'datetime')))
            ->add('expiration', 'datetime', array('attr' => array('class' => 'datetime')))
            ->add('text', 'textarea')
            ->add('add', 'submit')
            ->getForm();

        return array('form' => $form->createView(), 'title' => 'Edit Announcement');
    }

    private function getAnnouncements() {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('BioInfoBundle:Announcement');
        // $query = $em->createQuery(
        //     'SELECT a FROM BioInfoBundle:Announcement a 
        //     WHERE a.expiration > :now AND a.timestamp < :now
        //     ORDER BY a.expiration ASC')
        //         ->setParameter('now', new \DateTime());

        // $announcements = $query->getResult();
        $announcements = $repo->findBy(array());
        return $announcements;
    }
}
