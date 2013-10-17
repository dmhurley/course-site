<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serial;


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
     * @Serial\Exclude()
     */
    private $timecard;

    /**
     * @ORM\ManyToOne(targetEntity="\Bio\UserBundle\Entity\AbstractUserStudent")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     * @Serial\MaxDepth(2)
     **/
    private $student;


    /**
     * @ORM\ManyToOne(targetEntity="Exam")
     * @ORM\JoinColumn(name="examID", referencedColumnName="id", onDelete="CASCADE")
     * @Serial\MaxDepth(2)
     **/
    private $exam;

    /**
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="testTaker", cascade={"remove"})
     * @Serial\MaxDepth(1)
     */
    private $answers;

/*************** ASSIGNMENT VARIABLES *************/
    
    /**
     * @ORM\ManyToMany(targetEntity="TestTaker", inversedBy="isGrading")
     * @ORM\JoinTable(name="assigned",
     *      joinColumns={@ORM\JoinColumn(name="your_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="their_id", referencedColumnName="id", onDelete="CASCADE")}
     *  )
     * @Serial\Exclude()
     */
    private $assigned;  // who you're assigned to grade

    /**
     * @ORM\ManyToMany(targetEntity="TestTaker", inversedBy="gradedBy")
     * @ORM\JoinTable(name="graded",
     *      joinColumns={@ORM\JoinColumn(name="your_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="their_id", referencedColumnName="id", onDelete="CASCADE")}
     *  )
     * @Serial\Exclude()
     */
    private $graded;    // who you have graded

    /**
     * @ORM\ManyToMany(targetEntity="TestTaker", mappedBy="assigned")
     * @Serial\Exclude()
     */
    private $isGrading; // who is assigned to grade you

    /**
     * @ORM\ManyToMany(targetEntity="TestTaker", mappedBy="graded")
     * @Serial\Exclude()
     */
    private $gradedBy;  // who has graded you

    /**
     * @ORM\Column(name="gradedNum", type="integer")
     */
    private $gradedNum; // number you have/are grading

    /**
     * @ORM\Column(name="gradedByNum", type="integer")
     */
    private $gradedByNum;// number who are/have graded you

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->assigned = new \Doctrine\Common\Collections\ArrayCollection();
        $this->graded = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isGrading = new \Doctrine\Common\Collections\ArrayCollection();
        $this->gradedBy = new \Doctrine\Common\Collections\ArrayCollection();
        $this->timecard = [[
                            'event' => 'created',
                            'time' => new \DateTime()
                            ]];
        $this->status = 1;
        $this->gradedNum = 0;
        $this->gradedByNum = 0;
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

    public function setTimestamp($value) {
        $this->timecard[] = $value;
    }

    public function getTimestamp($name, $field = 'name') {
        $returner = [];
        foreach($this->timecard as $event) {
            if (isset($event[$field]) && $event[$field] === $name) {
                $returner[] = $event;
            }
        }
        return $returner;
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
     * Set student
     *
     * @param \Bio\UserBundle\Entity\AbstractUserStudent $student
     * @return TestTaker
     */
    public function setStudent(\Bio\UserBundle\Entity\AbstractUserStudent $student = null)
    {
        $this->student = $student;
    
        return $this;
    }

    /**
     * Get student
     *
     * @return \Bio\UserBundle\Entity\AbstractUserStudent 
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
     * Set gradedNum
     *
     * @param integer $gradedNum
     * @return TestTaker
     */
    public function setGradedNum($gradedNum)
    {
        $this->gradedNum = $gradedNum;
    
        return $this;
    }

    /**
     * Get gradedNum
     *
     * @return integer 
     */
    public function getGradedNum()
    {
        return $this->gradedNum;
    }

    /**
     * Set gradedByNum
     *
     * @param integer $gradedByNum
     * @return TestTaker
     */
    public function setGradedByNum($gradedByNum)
    {
        $this->gradedByNum = $gradedByNum;
    
        return $this;
    }

    /**
     * Get gradedByNum
     *
     * @return integer 
     */
    public function getGradedByNum()
    {
        return $this->gradedByNum;
    }

    /**
     * Add assigned
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $assigned
     * @return TestTaker
     */
    public function addAssigned(\Bio\ExamBundle\Entity\TestTaker $assigned)
    {
        $this->assigned[] = $assigned;
        $this->gradedNum++;
    
        return $this;
    }

    /**
     * Remove assigned
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $assigned
     */
    public function removeAssigned(\Bio\ExamBundle\Entity\TestTaker $assigned)
    {
        $this->assigned->removeElement($assigned);
    }

    /**
     * Get assigned
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAssigned()
    {
        return $this->assigned;
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
     * Add isGrading
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $isGrading
     * @return TestTaker
     */
    public function addIsGrading(\Bio\ExamBundle\Entity\TestTaker $isGrading)
    {
        $this->isGrading[] = $isGrading;
        $this->gradedByNum++;
    
        return $this;
    }

    /**
     * Remove isGrading
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $isGrading
     */
    public function removeIsGrading(\Bio\ExamBundle\Entity\TestTaker $isGrading)
    {
        $this->isGrading->removeElement($isGrading);
    }

    /**
     * Get isGrading
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIsGrading()
    {
        return $this->isGrading;
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

    // called when someone is graded
    public function graded(\Bio\ExamBundle\Entity\TestTaker $graded) {
        $this->addGraded($graded);
        $this->removeAssigned($graded);
        $graded->addGradedBy($this);
        $graded->removeIsGrading($this);
        return $this;
    }
}