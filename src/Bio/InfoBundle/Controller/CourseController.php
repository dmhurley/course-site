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
use Bio\InfoBundle\Entity\Link;

/**
 * @Route("/admin/course")
 */
class CourseController extends Controller
{

    /**
     * @Route("/", name="info_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array ('title' => 'Course Info');
    }

    /**
     * @Route("/customlink", name="fake_link")
     * @Template("BioInfoBundle:Link:link.html.twig")
     */
    public function addLinkAction(Request $request) {
        $link = new Link();
        if ($request->query->get('title') && $request->query->get('route')) {
            $link->setTitle($request->query->get('title'))
                ->setAddress($this->get('router')->generate($request->query->get('route'), array(), true));
        }
        $form = $link->addToForm($this->createFormBuilder($link))
            ->setAction($this->generateUrl('view', array('entityName' => 'link')))            
            ->add('add', 'submit')
            ->getForm();

        $db = new Database($this, 'BioInfoBundle:Link');
        $links = $db->find(array(), array(), false);

        return array('form' => $form->createView(), 'links' => $links, 'title' => 'Edit Links');
    }

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
