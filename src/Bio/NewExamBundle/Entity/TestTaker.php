<?php

namespace Bio\NewExamBundle\Entity;

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
     * @ORM\ManyToMany(targetEntity="TestTaker", mappedBy="gradedBy")
     */
    private $grading;

    /** 
     * @ORM\ManyToMany(targetEntity="TestTaker", inversedBy="grading")
     * @ORM\JoinTable(name="graders",
     *      joinColumns={@ORM\JoinColumn(name="grader_id", referencedColumnName="id", onDelete="CASCADE")},
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
        $this->timecard[$status] = new \DateTime();
    
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
     * Constructor
     */
    public function __construct()
    {
        $this->grading = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gradedBy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add grading
     *
     * @param \Bio\NewExamBundle\Entity\TestTaker $grading
     * @return TestTaker
     */
    public function addGrading(\Bio\NewExamBundle\Entity\TestTaker $grading)
    {
        $this->grading[] = $grading;
    
        return $this;
    }

    /**
     * Remove grading
     *
     * @param \Bio\NewExamBundle\Entity\TestTaker $grading
     */
    public function removeGrading(\Bio\NewExamBundle\Entity\TestTaker $grading)
    {
        $this->grading->removeElement($grading);
    }

    /**
     * Get grading
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGrading()
    {
        return $this->grading;
    }

    /**
     * Add gradedBy
     *
     * @param \Bio\NewExamBundle\Entity\TestTaker $gradedBy
     * @return TestTaker
     */
    public function addGradedBy(\Bio\NewExamBundle\Entity\TestTaker $gradedBy)
    {
        $this->gradedBy[] = $gradedBy;
    
        return $this;
    }

    /**
     * Remove gradedBy
     *
     * @param \Bio\NewExamBundle\Entity\TestTaker $gradedBy
     */
    public function removeGradedBy(\Bio\NewExamBundle\Entity\TestTaker $gradedBy)
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
     * @param \Bio\NewExamBundle\Entity\Exam $exam
     * @return TestTaker
     */
    public function setExam(\Bio\NewExamBundle\Entity\Exam $exam = null)
    {
        $this->exam = $exam;
    
        return $this;
    }

    /**
     * Get exam
     *
     * @return \Bio\NewExamBundle\Entity\Exam 
     */
    public function getExam()
    {
        return $this->exam;
    }

    /**
     * Add answers
     *
     * @param \Bio\NewExamBundle\Entity\Answer $answers
     * @return TestTaker
     */
    public function addAnswer(\Bio\NewExamBundle\Entity\Answer $answers)
    {
        $this->answers[] = $answers;
    
        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Bio\NewExamBundle\Entity\Answer $answers
     */
    public function removeAnswer(\Bio\NewExamBundle\Entity\Answer $answers)
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
}