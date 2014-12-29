<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Info;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

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
     * @Route("/edit", name="edit_info")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function indexAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioInfoBundle:Info');
		$info = $db->findOne();

		if (!$info) {
			$info = new Info();
			$db->add($info);
		}

    	$form = $this->createFormBuilder($info)
    		->add('courseNumber', 'text', array('label' => 'Course Number:'))
    		->add('title', 'text', array('label' => 'Course Name:'))
    		->add('qtr', 'choice', array(
                'choices' => array(
    					'autumn' => 'Autumn',
    					'winter' => 'Winter',
    					'spring' => 'Spring',
    					'summer' => 'Summer'
    				), 
                'label' => 'Quarter'
                )
            )
    		->add('year', 'integer', array('label' => 'Year:'))
    		->add('email', 'email', array('label' => 'Email:'))
    		->add('save', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			try {
    				$db->close();
                    $flash->set('success', 'Course information updated.');
    			} catch (BioException $e) {
                    $flash->set('failure', 'Unable to save changes.');
    			}
    		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

        return array('form' => $form->createView(), 'title' => "Edit Course Information");
    }
}
