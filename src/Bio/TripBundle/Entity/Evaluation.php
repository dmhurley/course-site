<?php

namespace Bio\TripBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Eval
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class Evaluation {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\Bio\StudentBundle\Entity\Student")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="Trip", inversedBy="evals")
     * @ORM\JoinColumn(name="tripID", referencedColumnName="id")
     */
    private $trip;

    /**
     * @var string
     *
     * @ORM\Column(name="eval", type="text")
     */
    private $eval;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     */
    private $timestamp;


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
     * Set eval
     *
     * @param string $eval
     * @return Eval
     */
    public function setEval($eval)
    {
        $this->eval = $eval;
    
        return $this;
    }

    /**
     * Get eval
     *
     * @return string 
     */
    public function getEval()
    {
        return $this->eval;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     * @return Eval
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    
        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set student
     *
     * @param \Bio\StudentBundle\Entity\Student $student
     * @return Evaluation
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
     * Set trip
     *
     * @param \Bio\TripBundle\Entity\Trip $trip
     * @return Evaluation
     */
    public function setTrip(\Bio\TripBundle\Entity\Trip $trip = null)
    {
        $this->trip = $trip;
    
        return $this;
    }

    /**
     * Get trip
     *
     * @return \Bio\TripBundle\Entity\Trip 
     */
    public function getTrip()
    {
        return $this->trip;
    }
}