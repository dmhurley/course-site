<?php

namespace Bio\NewExamBundle\Entity;

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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="time")
     */
    private $end;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @ORM\ManyToMany(targetEntity="Question")
     * @ORM\JoinTable(name="exam_questions",
     *      joinColumns={@ORM\JoinColumn(name="test_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="CASCADE")})
     */
    private $questions;

    public function __construct() {
        $this->date = new \DateTime();
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
     * Set date
     *
     * @param \DateTime $date
     * @return Exam
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return Exam
     */
    public function setStart($start)
    {
        $this->start = $start;
    
        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Exam
     */
    public function setEnd($end)
    {
        $this->end = $end;
    
        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     * @return Exam
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    
        return $this;
    }

    /**
     * Get duration
     *
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Add questions
     *
     * @param \Bio\NewExamBundle\Entity\Question $questions
     * @return Exam
     */
    public function addQuestion(\Bio\NewExamBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;
    
        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Bio\NewExamBundle\Entity\Question $questions
     */
    public function removeQuestion(\Bio\NewExamBundle\Entity\Question $questions)
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
}