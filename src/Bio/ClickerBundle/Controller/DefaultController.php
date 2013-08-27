<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\StudentBundle\Entity\Student;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/admin/clicker")
 */
class DefaultController extends Controller {
    /**
     * @Route("/", name="clicker_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Clickers');
    }

    /**
     * @Route("/../../clicker/register")
     * @Template()
     */
    public function registerAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('cid', 'text', array('label' => "Clicker ID:", 'constraints' => array(new Assert\Regex("/^[0-9A-Fa-f]{6}$/"), new Assert\NotBlank()), 'attr' => array('pattern' => '[0-9A-Fa-f]{6}', 'title' => '6 digit clicker ID')))
    		->add('Register', 'submit')
    		->getForm();

        $blankForm = clone $form;

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
            $clicker = new Clicker();
    		
    		if ($form->isValid()){
                $student = $this->get('security.context')->getToken()->getUser();
	    		
				$db = new Database($this, 'BioClickerBundle:Clicker');
                if ($dbClicker = $db->findOne(array('student' => $student))){
                    $request->getSession()->getFlashBag()->set('success', "Clicker ID changed to #".$form->get('cid')->getData());
                    $clicker = $dbClicker;
                } else {
                    $request->getSession()->getFlashBag()->set('success', "Clicker ID #".$form->get('cid')->getData()." registered.");
                    $db->add($clicker);
                    $clicker->setStudent($student);
                }
				$clicker->setCid($form->get('cid')->getData());

				try {
					$db->close();
				} catch (BioException $e) {
					$request->getSession()->getFlashBag()->set('failure', "Someone else is already registered to that clicker.");
					$request->getSession()->getFlashBag()->get('success'); // remove the successful flash message that was set earlier
				}
                $form = $blankForm;

	    	} else {
	    		$request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
	    	}
    	}

        return array('form' => $form->createView(), 'title' => "Register Clicker");
    }

    /**
     * @Route("/download", name="download_list")
     * @Template()
     */
    public function downloadAction(Request $request) {
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=clickerReg.xls");
        header("Content-Type: application/octet-stream; "); 
        header("Content-Transfer-Encoding: binary");

        $db = new Database($this, 'BioClickerBundle:Clicker');
        $clickers = $db->find(array(), array(), false);

    	echo "Last Name\tFirst Name\tclicker ID\tStudent ID\n";
    	foreach ($clickers as $clicker) {
    		echo $clicker->getStudent()->getLName()."\t";
    		echo $clicker->getStudent()->getFName()."\t";
    		echo $clicker->getCid()."\t";
    		echo $clicker->getStudent()->getSid()."\n";
    	}

	    return array('test' => '');
    }

    /**
     * @Route("/clear", name="clear_list")
     * @Template()
     */
    public function clearAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('confirmation', 'checkbox', array('constraints' => new Assert\True(array('message' => 'You must confirm.'))))
    		->add('clear', 'submit', array('label' => 'Clear Clickers'))
    		->getForm();
        $blankForm = clone $form;
    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$db = new Database($this, 'BioClickerBundle:Clicker');
                $db->truncate();

		        $request->getSession()->getFlashBag()->set('success', 'All clicker registrations cleared.');
                $form = $blankForm;
    		} else {
                $request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => 'Clear Registrations');
    }
}
