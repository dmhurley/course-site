<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Info;

/**
 * @Route("/course")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="edit_info")
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
}
