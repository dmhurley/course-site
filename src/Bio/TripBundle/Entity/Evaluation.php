<?php

namespace Bio\TripBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evaluation
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
     * @ORM\JoinColumn(name="tripID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $trip;

    /**
     * @ORM\ManyToMany(targetEntity="Response")
     * @ORM\JoinTable(name="eval_answers",
     *      joinColumns={@ORM\JoinColumn(name="eval_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="answer_id", referencedColumnName="id")}
     *      )
     */
    private $answers;

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
     * @return Response
     */
    public function setResponse($eval)
    {
        $this->eval = $eval;
    
        return $this;
    }

    /**
     * Get eval
     *
     * @return string 
     */
    public function getResponse()
    {
        return $this->eval;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     * @return Response
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add answers
     *
     * @param \Bio\TripBundle\Entity\Response $answers
     * @return Evaluation
     */
    public function addResponse(\Bio\TripBundle\Entity\Response $answers)
    {
        $this->answers[] = $answers;
    
        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Bio\TripBundle\Entity\Response $answers
     */
    public function removeResponse(\Bio\TripBundle\Entity\Response $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getResponses()
    {
        return $this->answers;
    }
}