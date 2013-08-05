<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TestTaker
 *
 * @ORM\Table()
 * @ORM\Entity
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
     * @ORM\ManyToMany(targetEntity="TestTaker")
     * @ORM\JoinTable(name="graded",
     *      joinColumns={@ORM\JoinColumn(name="you_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $graded;

    // necessary in case someone gets dropped and are removed from the graded column
    // records that at least this user graded someone even though there's no 
    /**
     * @var integer
     *
     * @ORM\Column(name="numGraded", type="integer")
     */
    private $numGraded;

    /**
     * @ORM\ManyToOne(targetEntity="TestTaker")
     * @ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $grading;

    /** 
     * @ORM\ManyToMany(targetEntity="TestTaker")
     * @ORM\JoinTable(name="graded_by",
     *      joinColumns={@ORM\JoinColumn(name="you_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="target_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $gradedBy;

    /**
     * @ORM\ManyToOne(targetEntity="\Bio\StudentBundle\Entity\Student")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="Exam")
     * @ORM\JoinColumn(name="examID", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $exam;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="testTaker", cascade={"remove"})
     */
    private $answers;

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
        $this->status = $status;
        
        if (!isset($this->timecard[$status])) {
            $this->timecard[$status] = new \DateTime();
        }
    
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
    public function getTimecard()
    {
        return $this->timecard;
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

    /**
     * Get vars
     *
     * @return array 
     */
    public function getVars()
    {
        return $this->vars;
    }

    public function getVar($key) {
        return $this->vars[$key];
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->graded = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gradedBy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->numGraded = 0;
    }
    
    /**
     * Add gradedBy
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $gradedBy
     * @return TestTaker
     */
    public function addGradedBy(\Bio\ExamBundle\Entity\TestTaker $gradedBy)
    {
        $this->gradedBy[] = $gradedBy;
    
        return $this;
    }

    /**
     * Remove gradedBy
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $gradedBy
     */
    public function removeGradedBy(\Bio\ExamBundle\Entity\TestTaker $gradedBy)
    {
        $this->gradedBy->removeElement($gradedBy);
    }

    /**
     * Get gradedBy
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGradedBy()
    {
        return $this->gradedBy;
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
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    public function getAnswer(\Bio\ExamBundle\Entity\Question $question) {
        foreach ($this->answers as $answer) {
            if ($answer->getQuestion() === $question) {
                return $answer;
            }
        }
        return null;
    }

    /**
     * Add graded
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $graded
     * @return TestTaker
     */
    public function addGraded(\Bio\ExamBundle\Entity\TestTaker $graded)
    {
        $this->graded[] = $graded;
        $this->numGraded++;
    
        return $this;
    }

    /**
     * Remove graded
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $graded
     */
    public function removeGraded(\Bio\ExamBundle\Entity\TestTaker $graded)
    {
        $this->graded->removeElement($graded);
    }

    /**
     * Get graded
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGraded()
    {
        return $this->graded;
    }

    /**
     * Set grading
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $grading
     * @return TestTaker
     */
    public function setGrading(\Bio\ExamBundle\Entity\TestTaker $grading = null)
    {
        $this->grading = $grading;
    
        return $this;
    }

    /**
     * Get grading
     *
     * @return \Bio\ExamBundle\Entity\TestTaker 
     */
    public function getGrading()
    {
        return $this->grading;
    }

    public function getNumGraded() {
        return $this->numGraded;
    }

    /**
     * Set numGraded
     *
     * @param integer $numGraded
     * @return TestTaker
     */
    public function setNumGraded($numGraded)
    {
        $this->numGraded = $numGraded;
    
        return $this;
    }
}