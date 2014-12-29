<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Bio\DataBundle\Objects\Database;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Person
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Person extends Base
{

    /**
     * @var string
     *
     * @ORM\Column(name="fName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $fName;

    /**
     * @var string
     *
     * @ORM\Column(name="lName", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="bldg", type="string", length=255)
     */
    private $bldg;

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255)
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Choice(choices={"instructor", "ta", "coordinator"}, message="Choose a valid position.")
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="Hours", mappedBy="person", cascade={"remove"})
     */
    private $hours;

    public function __construct() {
        $this->products = new ArrayCollection();
    }

    public function getFullName() {
        return $this->getFName()." ".$this->getLName();
    }

    /**
     * Set fName
     *
     * @param string $fName
     * @return Person
     */
    public function setFName($fName)
    {
        $this->fName = $fName;
    
        return $this;
    }

    /**
     * Get fName
     *
     * @return string 
     */
    public function getFName()
    {
        return ucfirst($this->fName);
    }

    /**
     * Set lName
     *
     * @param string $lName
     * @return Person
     */
    public function setLName($lName)
    {
        $this->lName = $lName;
    
        return $this;
    }

    /**
     * Get lName
     *
     * @return string 
     */
    public function getLName()
    {
        return ucfirst($this->lName);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Person
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
     * Set bldg
     *
     * @param string $bldg
     * @return Person
     */
    public function setBldg($bldg)
    {
        $this->bldg = $bldg;
    
        return $this;
    }

    /**
     * Get bldg
     *
     * @return string 
     */
    public function getBldg()
    {
        return $this->bldg;
    }

    /**
     * Set room
     *
     * @param string $room
     * @return Person
     */
    public function setRoom($room)
    {
        $this->room = $room;
    
        return $this;
    }

    /**
     * Get room
     *
     * @return string 
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Person
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
     * Add hours
     *
     * @param \Bio\InfoBundle\Entity\Hours $hours
     * @return Person
     */
    public function addHour(\Bio\InfoBundle\Entity\Hours $hours)
    {
        $this->hours[] = $hours;
    
        return $this;
    }

    /**
     * Remove hours
     *
     * @param \Bio\InfoBundle\Entity\Hours $hours
     */
    public function removeHour(\Bio\InfoBundle\Entity\Hours $hours)
    {
        $this->hours->removeElement($hours);
    }

    /**
     * Get hours
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHours()
    {
        return $this->hours;
    }

    public function findSelf(Database $db, array $options = array(), array $orderBy = array('fName' => 'ASC', 'lName' => 'ASC')){
        return $db->find($options, $orderBy, false);
    }
}