<?php

namespace Bio\ScoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;

use Bio\ScoreBundle\Entity\Scores;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/scores", name="scores")
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
    			$filename = $form->get('file')->getData();
		    	$file = file($filename, FILE_IGNORE_NEW_LINES);
		    	if (count($file) === 1){
		    		$file = preg_split('/\r\n|\r|\n/', $file[0]);
		    	}
		    	$header = explode("\t", $file[0]); // get titles of tests. Column titles

		    	for($i = 1; $i < count($file); $i++) { // go down the rows, starting at the second one
		    		$data = explode("\t", $file[$i]);  // split row into columns
		    		$score = new Scores();
		    		$score->setSid($data[0]);
		    		$array = array();

		    		for ($j = 1; $j < count($data); $j++) { // go down columns starting at second one
		    			$array[$header[$j]] = $data[$j];    // entries to titles
		    		}

		    		$score->setScores($array);
		    		$db->add($score);
		    	}

		    	$db->close();
		    	$request->getSession()->getFlashBag()->set('success', 'Scores uploaded.');
		    } else {
		    	$request->getSession()->getFlashBag()->set('failure', 'Invalid form.');
		    }
	    }

    	$scores = $db->find(array(), array(), false);

        return array('form' => $form->createView(), 'scores' => $scores, 'title' => 'Scores');
    }
}
