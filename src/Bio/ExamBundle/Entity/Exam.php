<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Exam
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Exam
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="section", type="string", length=2)
     */
    private $section;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tdate", type="date")
     */
    private $tDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tstart", type="time")
     */
    private $tStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tend", type="time")
     */
    private $tEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="tduration", type="integer")
     */
    private $Tduration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gdate", type="date")
     */
    private $gDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gstart", type="time")
     */
    private $gStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gend", type="time")
     */
    private $gEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="gduration", type="integer")
     */
    private $gduration;

    /**
     * @ORM\ManyToMany(targetEntity="Question")
     * @ORM\JoinTable(name="exam_questions",
     *      joinColumns={@ORM\JoinColumn(name="test_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="CASCADE")})
     */
    private $questions;

    public function __construct() {
        $this->tDate = new \DateTime();
        $this->gDate = new \DateTime();
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Exam
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add questions
     *
     * @param \Bio\ExamBundle\Entity\Question $questions
     * @return Exam
     */
    public function addQuestion(\Bio\ExamBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;
    
        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Bio\ExamBundle\Entity\Question $questions
     */
    public function removeQuestion(\Bio\ExamBundle\Entity\Question $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    public function setQuestions($questions) {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($questions as $question) {
            $this->addQuestion($question);
        }

        return $this;
    }

    /**
     * Set tDate
     *
     * @param \DateTime $tDate
     * @return Exam
     */
    public function setTDate($tDate)
    {
        $this->tDate = $tDate;
    
        return $this;
    }

    /**
     * Get tDate
     *
     * @return \DateTime 
     */
    public function getTDate()
    {
        return $this->tDate;
    }

    /**
     * Set tStart
     *
     * @param \DateTime $tStart
     * @return Exam
     */
    public function setTStart($tStart)
    {
        $this->tStart = $tStart;
    
        return $this;
    }

    /**
     * Get tStart
     *
     * @return \DateTime 
     */
    public function getTStart()
    {
        return $this->tStart;
    }

    /**
     * Set tEnd
     *
     * @param \DateTime $tEnd
     * @return Exam
     */
    public function setTEnd($tEnd)
    {
        $this->tEnd = $tEnd;
    
        return $this;
    }

    /**
     * Get tEnd
     *
     * @return \DateTime 
     */
    public function getTEnd()
    {
        return $this->tEnd;
    }

    /**
     * Set Tduration
     *
     * @param integer $tduration
     * @return Exam
     */
    public function setTduration($tduration)
    {
        $this->Tduration = $tduration;
    
        return $this;
    }

    /**
     * Get Tduration
     *
     * @return integer 
     */
    public function getTduration()
    {
        return $this->Tduration;
    }

    /**
     * Set gDate
     *
     * @param \DateTime $gDate
     * @return Exam
     */
    public function setGDate($gDate)
    {
        $this->gDate = $gDate;
    
        return $this;
    }

    /**
     * Get gDate
     *
     * @return \DateTime 
     */
    public function getGDate()
    {
        return $this->gDate;
    }

    /**
     * Set gStart
     *
     * @param \DateTime $gStart
     * @return Exam
     */
    public function setGStart($gStart)
    {
        $this->gStart = $gStart;
    
        return $this;
    }

    /**
     * Get gStart
     *
     * @return \DateTime 
     */
    public function getGStart()
    {
        return $this->gStart;
    }

    /**
     * Set gEnd
     *
     * @param \DateTime $gEnd
     * @return Exam
     */
    public function setGEnd($gEnd)
    {
        $this->gEnd = $gEnd;
    
        return $this;
    }

    /**
     * Get gEnd
     *
     * @return \DateTime 
     */
    public function getGEnd()
    {
        return $this->gEnd;
    }

    /**
     * Set gduration
     *
     * @param integer $gduration
     * @return Exam
     */
    public function setGduration($gduration)
    {
        $this->gduration = $gduration;
    
        return $this;
    }

    /**
     * Get gduration
     *
     * @return integer 
     */
    public function getGduration()
    {
        return $this->gduration;
    }

    /**
     * Set section
     *
     * @param string $section
     * @return Exam
     */
    public function setSection($section)
    {
        $this->section = $section;
    
        return $this;
    }

    /**
     * Get section
     *
     * @return string 
     */
    public function getSection()
    {
        return $this->section;
    }
}