<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * Section
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("name")
 */
class Section extends Base
{

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^[A-Z][A-Z0-9]?$/")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="days", type="array")
     * @Assert\Choice(choices={"m", "tu", "w", "th", "f", "sa", "su"}, multiple=true, message="Choose a valid day.")
     */
    protected $days;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="time")
     * @Assert\Time()
     */
    protected $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="time")
     * @Assert\Time()
     */
    protected $end;

    /**
     * @var string
     *
     * @ORM\Column(name="bldg", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $bldg;

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $room;

    /**
     * Set name
     *
     * @param string $name
     * @return Section
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
     * Set days
     *
     * @param array $days
     * @return Section
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
        return $this->days;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return Section
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
     * @return Section
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
     * Set bldg
     *
     * @param string $bldg
     * @return Section
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
     * @return Section
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

     public function addToForm(FormBuilder $builder) {
        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
        $builder
            ->add('name', 'text', array(
                'label' => 'Name:',
                'attr' => array(
                    'pattern' => '^[A-Z][A-Z0-9]?$',
                    'title' => 'Valid capitalized section name.'
                    )
                )
            )
            ->add('days', 'choice', array(
                'label' => 'Day:',
                'choices' => array(
                    "m" => "Monday",
                    "tu" => "Tuesday",
                    "w" => "Wednesday",
                    "th" => "Thursday",
                    "f" => "Friday",
                    "sa" => "Saturday",
                    "su" => "Sunday"),
                'multiple' => true))
            ->add('start', 'time', array('label' => 'Start Time:'))
            ->add('end', 'time', array('label' => 'End Time:'))
            ->add('bldg', 'choice', array(
                'choices' => array_combine($array, $array),
                'validation_groups' => false,
                'label' => "Building:"
                )
            )
            ->add('room', 'text', array('label' => 'Room:'));

        return $builder;
    }

    public function findSelf($db, $options = array(), $orderBy = array('name' => 'ASC')){
        return $db->find($options, $orderBy, false);
    }
}