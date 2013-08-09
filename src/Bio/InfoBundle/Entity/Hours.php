<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;

/**
 * Hours
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Hours extends Base
{
    /**
     * @var string
     *
     * @ORM\Column(name="days", type="string", length=255)
     */
    private $days;

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
     * @var boolean
     *
     * @ORM\Column(name="byAppointment", type="boolean")
     */
    private $byAppointment;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="hours")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    private $person;

    /**
     * Set days
     *
     * @param array $days
     * @return Hours
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
     * @return Hours
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
     * @return Hours
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
     * Set byAppointment
     *
     * @param boolean $byAppointment
     * @return Hours
     */
    public function setByAppointment($byAppointment)
    {
        $this->byAppointment = $byAppointment;
    
        return $this;
    }

    /**
     * Get byAppointment
     *
     * @return boolean 
     */
    public function getByAppointment()
    {
        return $this->byAppointment;
    }

    /**
     * Set person
     *
     * @param \Bio\InfoBundle\Entity\Person $person
     * @return Hours
     */
    public function setPerson(\Bio\InfoBundle\Entity\Person $person = null)
    {
        $this->person = $person;
    
        return $this;
    }

    /**
     * Get person
     *
     * @return \Bio\InfoBundle\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }

    public function addToForm(FormBuilder $builder) {
        $builder->add('person', 'entity', array('class' => 'BioInfoBundle:Person', 'property' => 'fullName', 'label' => 'Instructor:'))
            ->add('days', 'text', array('label' => 'Days:'))
            ->add('start', 'time', array('label' => 'Start Time:'))
            ->add('end', 'time', array('label' => 'End Time:'))
            ->add('byAppointment', 'checkbox', array('required' => false, 'label' => 'By Appointment?'));

        return $builder;
    }
}