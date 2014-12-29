<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Bio\DataBundle\Objects\Database;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;


/**
 * Hours
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Assert\Callback(methods={"isDaysOrByAppointment"});
 */
class Hours extends Base
{
    /**
     * @var string
     *
     * @ORM\Column(name="days", type="string", length=255)
     */
    private $days;

    public function isDaysOrByAppointment(ExecutionContextInterface $context) {
        if (!$this->days && $this->byAppointment === false) {
            $context->addViolationAt('days', 'You must specify days.');
        }
    }

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="time")
     * @Assert\Time()
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="time")
     * @Assert\Time()
     */
    private $end;

    /**
     * @var boolean
     *
     * @ORM\Column(name="byAppointment", type="boolean")
     * @Assert\NotNull()
     */
    private $byAppointment;

    /**
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="hours")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * @Assert\NotNull()
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

    public function findSelf(Database $db, array $options = array(), array $orderBy = array()){
        return $db->find($options, $orderBy, false);
    }
}