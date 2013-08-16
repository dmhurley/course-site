<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Info
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Info
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
     * @ORM\Column(name="courseNumber", type="string", length=255)
     * @Assert\NotBlank(message="Cannot be blank.")
     * @Assert\Regex("/^[0-9]*$/")
     */
    private $courseNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank();
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="qtr", type="string", length=255)
     * @Assert\Choice(choices={"summer", "autumn", "winter", "spring"}, message="Choose a valid quarter.")
     */
    private $qtr;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer")
     * @Assert\Range(min=2000, max=2100)
     */
    private $year;

    /**
     * @var array
     *
     * @ORM\Column(name="days", type="array")
     */
    private $days;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startTime", type="time")
     * @Assert\Time()
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endTime", type="time")
     * @Assert\Time()
     */
    private $endTime;

    /**
     * @var string
     *
     * @ORM\Column(name="bldg", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $bldg;

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;


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
     * Set courseNumber
     *
     * @param string $courseNumber
     * @return Info
     */
    public function setCourseNumber($courseNumber)
    {
        $this->courseNumber = $courseNumber;
    
        return $this;
    }

    /**
     * Get courseNumber
     *
     * @return string 
     */
    public function getCourseNumber()
    {
        return $this->courseNumber;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Info
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
     * Set qtr
     *
     * @param string $qtr
     * @return Info
     */
    public function setQtr($qtr)
    {
        $this->qtr = $qtr;
    
        return $this;
    }

    /**
     * Get qtr
     *
     * @return string 
     */
    public function getQtr()
    {
        return $this->qtr;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return Info
     */
    public function setYear($year)
    {
        $this->year = $year;
    
        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set days
     *
     * @param string $days
     * @return Info
     */
    public function setDays($days)
    {
        $this->days = $days;
    
        return $this;
    }

    /**
     * Get days
     *
     * @return string 
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     * @return Info
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    
        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime 
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     * @return Info
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    
        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime 
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set bldg
     *
     * @param string $bldg
     * @return Info
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
     * @return Info
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
     * Set email
     *
     * @param string $email
     * @return Info
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
}