<?php

namespace Bio\SwitchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Request
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Request
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
     * @ORM\OneToOne(targetEntity="\Bio\UserBundle\Entity\AbstractUserStudent")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="\Bio\InfoBundle\Entity\Section")
     * @ORM\JoinColumn(name="current_section_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $current;

    /**
     * @ORM\ManyToMany(targetEntity="\Bio\InfoBundle\Entity\Section")
     * @ORM\JoinTable(name="requested_sections",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="sections_id", referencedColumnName="id", onDelete="CASCADE")})
     */
    private $want;

    /**
     * @ORM\OneToOne(targetEntity="Request")
     * @ORM\JoinColumn(name="request_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $match;

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
     * @return Request
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->want = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set student
     *
     * @param \Bio\StudentBundle\Entity\Student $student
     * @return Request
     */
    public function setStudent($student = null)
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
     * Set current
     *
     * @param \Bio\InfoBundle\Entity\Section $current
     * @return Request
     */
    public function setCurrent(\Bio\InfoBundle\Entity\Section $current = null)
    {
        $this->current = $current;
    
        return $this;
    }

    /**
     * Get current
     *
     * @return \Bio\InfoBundle\Entity\Section 
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Add want
     *
     * @param \Bio\InfoBundle\Entity\Section $want
     * @return Request
     */
    public function addWant(\Bio\InfoBundle\Entity\Section $want)
    {
        $this->want[] = $want;
    
        return $this;
    }

    /**
     * Remove want
     *
     * @param \Bio\InfoBundle\Entity\Section $want
     */
    public function removeWant(\Bio\InfoBundle\Entity\Section $want)
    {
        $this->want->removeElement($want);
    }

    /**
     * Get want
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWant()
    {
        return $this->want;
    }

    public function setWants($wants) {
        $this->want = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($wants as $want) {
            $this->addWant($want);
        }

        return $this;
    }

    /**
     * Set match
     *
     * @param \Bio\SwitchBundle\Entity\Request $match
     * @return Request
     */
    public function setMatch(\Bio\SwitchBundle\Entity\Request $match = null)
    {   
        $this->match = $match;
    
        return $this;
    }

    /**
     * Get match
     *
     * @return \Bio\SwitchBundle\Entity\Request 
     */
    public function getMatch()
    {
        return $this->match;
    }
}