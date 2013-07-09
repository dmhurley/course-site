<?php

namespace Bio\ClickerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\StudentBundle\Entity\Student;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/admin/clicker")
 * @Template()
 */
class DefaultController extends Controller {
    /**
     * @Route("/register", name="register_clicker")
     * @Template()
     */
    public function registerAction(Request $request) {
    	$clicker = new Clicker();
    	$form = $this->createFormBuilder($clicker)
    		->add('cid', 'text', array('label' => "Clicker ID:"))
    		->add('sid', 'text', array('label' => "Student ID:"))
    		->add('lName', 'text', array('label' => "Last Name:", 'mapped' => false))
    		->add('Register', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$this->update();
    		$form->handleRequest($request);
    		
    		if ($form->isValid()){
                $db = new Database($this, 'BioStudentBundle:Student');
                $student = $db->findOne(array('sid' => $form->get('sid')->getData(), 'lName' => $form->get('lName')->getData()));
	    		
	    		if ($student) {		// if student exists
					$clickerDb = new Database($this, 'BioClickerBundle:Clicker');
                    $clicker = $clickerDb->findOne(array('sid' => $student->getSid()));
                    $request->getSession()->getFlashBag()->set('success', "Clicker ID changed to #".$form->get('cid')->getData());
                    if (!$clicker) {
                        $clicker = new Clicker();
                        $clickerDb->add($clicker);
                        $request->getSession()->getFlashBag()->set('success', "Clicker ID #".$form->get('cid')->getData()." registered.");
                    }
					$clicker->setCid($form->get('cid')->getData());
					$clicker->setSid($student->getSid());

					try {
						$db->close();
					} catch (BioException $e) {
						$request->getSession()->getFlashBag()->set('failure', "Someone else is already registered to that clicker.");
						$request->getSession()->getFlashBag()->get('success');
					}

	    		} else {
	    			$request->getSession()->getFlashBag()->set('failure', 'We could not find that student.');
	    		}
	    	} else {
	    		$request->getSession()->getFlashBag()->set('failure', 'Invalid Clicker or Student ID.');
	    	}
    	}

        return array('form' => $form->createView(), 'title' => "Register Clicker");
    }

    /**
     * @Template()
     * @Route("/download", name="download_list")
     */
    public function downloadAction(Request $request) {
    	$this->update();
    	$tring = "Last Name\tFirst Name\tclicker ID\tStudent ID\n";
        $clickerDb = new Database($this, 'BioClickerBundle:Clicker');
        $studentDb = new Database($this, 'BioStudentBundle:Student');
    	$clickers = $clickerDb->find(array(), array(), false);
    	foreach ($clickers as $clicker) {
    		$student = $studentDb->findOne(array('sid' => $clicker->getSid()));
    		$tring.= $student->getLName()."\t";
    		$tring.= $student->getFName()."\t";
    		$tring.= $clicker->getCid()."\t";
    		$tring.= $clicker->getSid()."\n";
    	}
    	header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=clickerReg.xls");
		header("Content-Type: application/octet-stream; "); 
		header("Content-Transfer-Encoding: binary");

	    return array('test' => $tring);
    }

    /**
     * @Route("/clear", name="clear_list")
     * @Template()
     */
    public function clearAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('confirmation', 'checkbox')
    		->add('clear', 'submit', array('label' => 'Clear Clickers'))
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$db = new Database($this, 'BioClickerBundle:Clicker');
                $db->truncate();

		        $request->getSession()->getFlashBag()->set('success', 'All clicker registrations cleared.');
    		}
    	}

    	return array('form' => $form->createView(), 'title' => 'Clear Registrations');
    }

    public function update() {
    	$em = $this->getDoctrine()->getManager();
    	$query = $em->createQuery(
    		'DELETE FROM BioClickerBundle:Clicker c WHERE c.sid NOT IN (SELECT d.sid FROM BioStudentBundle:Student d)'
    	);
    	$query->getResult();
    }
}
