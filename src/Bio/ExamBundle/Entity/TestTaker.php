<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * TestTaker
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity({"sid", "exam"})
 */
class TestTaker
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="\Bio\StudentBundle\Entity\Student")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $student;

    /**
     * @var array
     *
     * @ORM\Column(name="grading", type="array")
     */
    private $grading;

    /**
     * @var integer
     * @ORM\Column(name="graded", type="integer")
     */
    private $gradedCount;

    /**
     * @ORM\ManyToOne(targetEntity="Exam")
     * @ORM\JoinColumn(name="examID", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $exam;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var array
     *
     * @ORM\Column(name="timecard", type="array")
     */
    private $timecard;

    /**
     * @var array
     *
     * @ORM\Column(name="vars", type="array")
     */
    private $vars;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="testTaker", cascade={"remove"})
     */
    private $answers;

    public function __construct() {
        $this->vars = array();
        $this->timecard = array();
        $this->answers = array();
        $this->grading = array();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return TestTaker
     */
    public function setStatus($status)
    {   
        if (!isset($this->timecard[$status])) {
            $this->timecard[$status] = new \DateTime();
        }
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set vars
     *
     * @param array $vars
     * @return TestTaker
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
    
        return $this;
    }

    public function setVar($key, $value) {
        $this->vars[$key] = $value;

        return $this;
    }

    public function getVar($key) {
        return $this->vars[$key];
    }

    public function hasVar($key) {
        return isset($this->vars[$key]);
    }

    /**
     * Get vars
     *
     * @return array 
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Set timecard
     *
     * @param array $timecard
     * @return TestTaker
     */
    public function setTimecard($timecard)
    {
        $this->timecard = $timecard;
    
        return $this;
    }

    /**
     * Get timecard
     *
     * @return array 
     */
    public function getTimecard($id = null)
    {   
        if ($id) {
            return $this->timecard[$id];
        }
        return $this->timecard;
    }

    /**
     * Set answers
     *
     * @param array $answers
     * @return TestTaker
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;
    
        return $this;
    }

    /**
     * Get answers
     *
     * @return array 
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set grading
     *
     * @param array $grading
     * @return TestTaker
     */
    public function setGrading($grading)
    {
        $this->grading = $grading;
    
        return $this;
    }

    public function setGrader($grader, $graded = false) {
        $this->grading[$grader] = $graded;
    }

    /**
     * Get grading
     *
     * @return array 
     */
    public function getGrading()
    {
        return $this->grading;
    }

    /**
     * Set exam
     *
     * @param \Bio\ExamBundle\Entity\Exam $exam
     * @return TestTaker
     */
    public function setExam(\Bio\ExamBundle\Entity\Exam $exam = null)
    {
        $this->exam = $exam;
    
        return $this;
    }

    /**
     * Get exam
     *
     * @return \Bio\ExamBundle\Entity\Exam 
     */
    public function getExam()
    {
        return $this->exam;
    }

    /**
     * Set student
     *
     * @param \Bio\StudentBundle\Entity\Student $student
     * @return TestTaker
     */
    public function setStudent(\Bio\StudentBundle\Entity\Student $student = null)
    {
        $this->student = $student;
    
        return $this;
    }

    /**
     * Get student
     *
     * @return \Bio\StudentBundle\Entity\Student 
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Add answers
     *
     * @param \Bio\ExamBundle\Entity\Answer $answers
     * @return TestTaker
     */
    public function addAnswer(\Bio\ExamBundle\Entity\Answer $answers)
    {
        $this->answers[] = $answers;
    
        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Bio\ExamBundle\Entity\Answer $answers
     */
    public function removeAnswer(\Bio\ExamBundle\Entity\Answer $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Set gradedCount
     *
     * @param integer $gradedCount
     * @return TestTaker
     */
    public function setGradedCount($gradedCount)
    {
        $this->gradedCount = $gradedCount;
    
        return $this;
    }

    /**
     * Get gradedCount
     *
     * @return integer 
     */
    public function getGradedCount()
    {
        return $this->gradedCount;
    }

    public function incrementGraded() {
        $this->gradedCount++;

        return $this;
    }
}