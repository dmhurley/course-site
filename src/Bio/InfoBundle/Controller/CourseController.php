<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Info;
use Bio\InfoBundle\Entity\Hours;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/admin/course")
 */
class CourseController extends Controller
{
    /**
     * @Route("/edit", name="edit_info")
     * @Template()
     */
    public function indexAction(Request $request) {
        $db = new Database($this, 'BioInfoBundle:Info');
		$info = $db->findOne();

		if (!$info) {
			$info = new Info();
			$db->add($info);
		}

        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
    	$form = $this->createFormBuilder($info)
    		->add('courseNumber', 'text')
    		->add('title', 'text')
    		->add('qtr', 'choice', array('choices' => array(
    					'autumn' => 'Autumn',
    					'winter' => 'Winter',
    					'spring' => 'Spring',
    					'summer' => 'Summer'
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
    		->add('bldg', 'choice', array('choices' => array_combine($array, $array)))
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
    		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Not updated. Please fix any errors.');
            }
    	}

        return array('form' => $form->createView(), 'title' => "Edit Course Information");
    }
}
