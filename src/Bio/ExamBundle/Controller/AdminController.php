<?php

namespace Bio\ExamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/exam")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="exam_instruct")
     * @Template()
     */
    public function instructionAction()
    {
        return array('title' => "Practice Exams");
    }
}
