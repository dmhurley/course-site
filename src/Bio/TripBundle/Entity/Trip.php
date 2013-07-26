<?php

namespace Bio\TripBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trip
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Trip
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
     * @ORM\Column(name="shortSum", type="text")
     */
    private $shortSum;

    /**
     * @var string
     *
     * @ORM\Column(name="longSum", type="text")
     */
    private $longSum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime")
     */
    private $end;

    /**
     * @var integer
     *
     * @ORM\Column(name="max", type="integer")
     */
    private $max;

    /**
     * @ORM\ManyToMany(targetEntity="\Bio\StudentBundle\Entity\Student")
     * @ORM\JoinTable(name="trips_students",
     *      joinColumns={@ORM\JoinColumn(name="trip_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="student_id", referencedColumnName="id", onDelete="CASCADE",
     *      unique=true)})
     */
    private $students;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;


    public function __construct() {
        $this->start = new \DateTime();
        $this->end = new \DateTime('+1 day');
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Trip
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
     * Set shortSum
     *
     * @param string $shortSum
     * @return Trip
     */
    public function setShortSum($shortSum)
    {
        $this->shortSum = $shortSum;
    
        return $this;
    }

    /**
     * Get shortSum
     *
     * @return string 
     */
    public function getShortSum()
    {
        return $this->shortSum;
    }

    /**
     * Set longSum
     *
     * @param string $longSum
     * @return Trip
     */
    public function setLongSum($longSum)
    {
        $this->longSum = $longSum;
    
        return $this;
    }

    /**
     * Get longSum
     *
     * @return string 
     */
    public function getLongSum()
    {
        return $this->longSum;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return Trip
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
     * @return Trip
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
     * Set max
     *
     * @param integer $max
     * @return Trip
     */
    public function setLim($max)
    {
        $this->max = $max;
    
        return $this;
    }

    /**
     * Get max
     *
     * @return integer 
     */
    public function getLim()
    {
        return $this->max;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Trip
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set max
     *
     * @param integer $max
     * @return Trip
     */
    public function setMax($max)
    {
        $this->max = $max;
    
        return $this;
    }

    /**
     * Get max
     *
     * @return integer 
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Add students
     *
     * @param \Bio\StudentBundle\Entity\Student $students
     * @return Trip
     */
    public function addStudent(\Bio\StudentBundle\Entity\Student $students)
    {
        $this->students[] = $students;
    
        return $this;
    }

    /**
     * Remove students
     *
     * @param \Bio\StudentBundle\Entity\Student $students
     */
    public function removeStudent(\Bio\StudentBundle\Entity\Student $students)
    {
        $this->students->removeElement($students);
    }

    /**
     * Get students
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStudents()
    {
        return $this->students;
    }
}