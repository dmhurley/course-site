<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\InfoBundle\Entity\Info;
use Bio\InfoBundle\Form\InfoType;
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

        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
    	$form = $this->createForm(new InfoType(), null, array(
                    'action' => $this->generateUrl('global_entity', array(
                            'bundle' => 'info',
                            'entityName' => 'info'
                        )
                    )
                )
            )
    		->add('save', 'submit');

        return array('form' => $form->createView(), 'title' => "Edit Course Information");
    }
}
