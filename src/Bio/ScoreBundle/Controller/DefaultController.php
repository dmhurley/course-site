<?php

namespace Bio\ScoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Bio\ScoreBundle\Entity\Scores;
use Bio\ScoreBundle\Entity\Stat;

/**
 * @Route("/admin/scores")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="scores_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => 'Scores');
    }

    /**
     * @Route("/upload", name="scores")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $flash = $request->getSession()->getFlashBag();

    	$form = $this->createFormBuilder()
    		->add('file', 'file', array('label' => '.txt File:'))
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
		    		$flash->set('success', 'Scores uploaded.');
		    	} catch (BioException $e) {
		    		$flash->set('failure', $e->getMessage());
		    	}
		    } else {
		    	$flash->set('failure', 'Invalid form.');
		    }
		    return $this->redirect($this->generateUrl('scores'));
	    }

    	$scores = $db->find(array(), array(), false);
    	$db = new Database($this, 'BioScoreBundle:Stat');
    	$stats = $db->find(array(), array(), false);

        return array(
            'form' => $form->createView(),
            'scores' => $scores, 'stats'=>$stats,
            'title' => 'Scores'
            );
    }

    /**
     * @Route("/../../scores", name="find_score")
     * @Template()
     */
    public function findAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();
        $student = $this->get('security.context')->getToken()->getUser();

        $db = new Database($this, 'BioScoreBundle:Scores');
        $score = $db->findOne(array('sid' => $student->getSid()), array(), false);

        if (!$score) {
            $flash->set('failure', 'Could not find any scores.');
            return $this->redirect($this->generateUrl('main_page'));
        }

        $db = new Database($this, 'BioScoreBundle:Stat');
        $stats = $db->find(array(), array(), false);

        return array(
            'score' => $score,
            'stats' => $stats,
            'title' => 'View Your Scores'
            );
    }

    private function uploadStudentScores($file, $db) {
    	$entities = $db->truncate();
    	$tempDb = new Database($this, 'BioScoreBundle:Stat');
    	$stats = $tempDb->truncate();
        $sids = [];

        $header = explode("\t", $file[0]); // get titles of tests. Column titles

        try {
            $lineCount = count($file);
	    	for($i = 1; $i < $lineCount; $i++) { // go down the rows, starting at the second one
		    	$data = explode("\t", $file[$i]);  // split row into columns
		    	if (count($header) !== count($data)) {
		    		throw new BioException('Improperly formatted file. All rows must have the same number of columns.');
		    	}
		    	if (!preg_match('/[0-9]/', $data[0])) {
		    		$db->add($this->createStat($header, $data));
		    	} else {
			    	$sid = $data[0].'';
			    	if (in_array($sid, $sids)) {
			    		throw new BioException('The file contained duplicate Student IDs.');
		    		}
		    		$score = new Scores();
		    		while (strlen($sid) < 7) {
	                	$sid = "0".$sid;
	            	}
		    		$score->setSid($sid);
		    		$array = array();

                    $columnCount = count($data);
		    		for ($j = 1; $j < $columnCount; $j++) { // go down columns starting at second one
		    			$array[$header[$j]] = $data[$j];    // entries to titles
		    		}
		    		$score->setScores($array);
	    			$sids[] = $sid;
	    			$db->add($score);
		    	}
	    	}
	    } catch (\Exception $e) {
	    	$db->clear();
            $entityCount = count($entities);
			for ($j = 0; $j < $entityCount; $j++) {
                $db->add($entities[$j]);
            }
            $statCount = count($stats);
            for ($j = 0; $j < $statCount; $j++) {
            	$db->add($stats[$j]);
            }
            $db->close();
            throw $e;
	    }
    	
    	$db->close("Could not persist scores to database.");
    }

    private function createStat($header, $data) {
    	$stat = new Stat();
    	$stat->setName($data[0]);

    	$array = array();
        $dataCount = count($data);
    	for ($j = 1; $j < $dataCount; $j++) {
    		$array[$header[$j]] = $data[$j];
    	}
    	$stat->setStats($array);

    	return $stat;
    }
}
