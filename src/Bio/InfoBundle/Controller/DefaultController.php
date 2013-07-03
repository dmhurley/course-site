<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Info;
use Bio\InfoBundle\Entity\Announcement;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

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
        $db = new Database($this, 'BioInfoBundle:Info');
		$info = $db->findOne();

		if (!$info) {
			$info = new Info();
			$db->add($info);
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
    				$db->close("Something broke...");
                    $request->getSession()->getFlashBag()->set('success', 'Course information updated.');
    			} catch (BioException $e) {
                    $request->getSession()->getFlashBag()->set('failure', $e->getMessage());
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

        $anns = $db->find(array(), array('expiration' => 'DESC'));
        return array('form' => $form->createView(),'anns' => $anns, 'title' => 'Edit Announcements');
    }

    /**
     * @Route("/delete/announcement", name="delete_announcement")
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
     * @Route("/edit/announcement", name="edit_announcement")
     * @Template()
     */
    public function editAction(Request $request) {
        $ann = new Announcement(); // her??
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
