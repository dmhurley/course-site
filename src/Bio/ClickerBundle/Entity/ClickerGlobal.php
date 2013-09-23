<?php

namespace Bio\ClickerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ClickerGlobal
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ClickerGlobal
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
     * @var boolean
     *
     * @ORM\Column(name="notifications", type="boolean")
     * @Assert\NotBlank()
     */
    private $notifications;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     * @Assert\DateTime()
     * @Assert\NotBlank()
     */
    private $start;


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
     * Set notifications
     *
     * @param boolean $notifications
     * @return ClickerGlobal
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    
        return $this;
    }

    /**
     * Get notifications
     *
     * @return boolean 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return ClickerGlobal
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
}
