<?php

namespace Bio\ScoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;

use Bio\ScoreBundle\Entity\Scores;

/**
 * @Route("/admin/scores")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="scores")
     * @Template()
     */
    public function indexAction(Request $request)
    {
    	$form = $this->createFormBuilder()
    		->add('file', 'file', array('label' => 'CSV File:', 'mapped' => false))
    		->add('upload', 'submit')
    		->getForm();

    	$db = new Database($this, 'BioScoreBundle:Scores');

    	if ($request->getMethod() === "POST"){
    		$form->handleRequest($request);

    		if ($form->isValid() && file_exists($form->get('file')->getData())) {

		    	$file = file($form->get('file')->getData(), FILE_IGNORE_NEW_LINES);
		    	if (count($file) === 1){
		    		$file = preg_split('/\r\n|\r|\n/', $file[0]);
		    	}

		    	try {
		    		$this->uploadStudentScores($file, $db);
		    		$request->getSession()->getFlashBag()->set('success', 'Scores uploaded.');
		    	} catch (BioException $e) {
		    		$request->getSession()->getFlashBag()->set('failure', $e->getMessage());
		    	}
		    } else {
		    	$request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
		    }
	    }

    	$scores = $db->find(array(), array(), false);

        return array('form' => $form->createView(), 'scores' => $scores, 'title' => 'Scores');
    }

    /**
     * @Route("/../../scores/find")
     * @Template("BioScoreBundle:Default:index.html.twig")
     */
    public function findAction(Request $request) {
    	$form = $this->createFormBuilder()
    		->add('sid', 'text', array('label' => 'Student ID:', 'mapped' => false))
    		->add('find', 'submit')
    		->getForm();
    	$scores = array();
    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);

    		if ($form->isValid()) {
    			$db = new Database($this, 'BioScoreBundle:Scores');
    			$scores = $db->find(array('sid' => $form->get('sid')->getData()), array(), false);
    		}
    	}

    	return array('scores'=>$scores, 'form' => $form->createView(), 'title' => 'View Your Scores');
    }

    private function uploadStudentScores($file, $db) {
    	$entities = $db->truncate();
        $sids = [];

        $header = explode("\t", $file[0]); // get titles of tests. Column titles

    	for($i = 1; $i < count($file); $i++) { // go down the rows, starting at the second one
	    	$data = explode("\t", $file[$i]);  // split row into columns
	    	$sid = $data[0].'';
	    	if (!in_array($sid, $sids)) {
	    		$score = new Scores();
	    		while (strlen($sid) < 7) {
                	$sid = "0".$sid;
            	}
	    		$score->setSid($sid);
	    		$array = array();

	    		for ($j = 1; $j < count($data); $j++) { // go down columns starting at second one
	    			$array[$header[$j]] = $data[$j];    // entries to titles
	    		}
	    		$score->setScores($array);
    			$sids[] = $sid;
    			$db->add($score);
    		} else {
    			$db->clear();
    			for ($j = 0; $j < count($entities); $j++) {
                    $db->add($entities[$j]);
                }
                $db->close();
                throw new BioException("The file contained duplicated Student IDs.");
    		}
    	}
    	
    	$db->close("Could not persist scores to database.");
    }
}
