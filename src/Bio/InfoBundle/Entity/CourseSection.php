<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation as Serial;


/**
 * CourseSection
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("name")
 */
class CourseSection extends Base
{
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^[A-Z]$/")
     */
    protected $name;

    /**
     * @var array
     *
     * @ORM\Column(name="days", type="array")
     * @Assert\Choice(choices={"m", "tu", "w", "th", "f", "sa", "su"}, multiple=true,  message="Choose a valid day.")
     */
    private $days;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="time")
     * @Assert\Time()
     * @Serial\Type("DateTime<'U'>")
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="time")
     * @Assert\Time()
     * @Serial\Type("DateTime<'U'>")
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

    public function __construct() {
        $this->days = [];
    }

    /**
     * Set days
     *
     * @param array $days
     * @return CourseSection
     */
    public function setDays($days)
    {
        $this->days = $days;
    
        return $this;
    }

    /**
     * Get days
     *
     * @return array 
     */
    public function getDays()
    {
        $array = $this->days;
        usort($array, function($a, $b) {
            $days = array('m'=>0, 'tu'=>1, 'w'=>2, 'th'=>3, 'f'=>4, 'sa'=>5, 'su'=>6);
            return $days[$a] - $days[$b];
        });
        return $array;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     * @return CourseSection
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
     * @return CourseSection
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
     * @return CourseSection
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
     * @return CourseSection
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
     * Set name
     *
     * @param string $name
     * @return CourseSection
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
}