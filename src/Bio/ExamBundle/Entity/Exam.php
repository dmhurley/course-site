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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \Date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var \Time
     *
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @var \Time
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
     *      joinColumns={@ORM\JoinColumn(name="testID", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="questionID", referencedColumnName="id", onDelete="CASCADE")})
     */
    private $questions;

    public function __construct() {
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
     * Set name
     *
     * @param string $name
     * @return Exam
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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

    public function setQuestions($questions) {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($questions as $q) {
            $this->addQuestion($q);
        }
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
}