<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serial;

/**
 * Announcement
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Announcement extends Base
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     * @Assert\DateTime()
     * @Serial\Type("DateTime<'U'>")
     */
    private $timestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiration", type="datetime")
     * @Assert\DateTime()
     * @Serial\Type("DateTime<'U'>")
     */
    private $expiration;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     * @Assert\NotBlank()
     */
    private $text;

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     * @return Announcement
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
     * Set expiration
     *
     * @param \DateTime $expiration
     * @return Announcement
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    
        return $this;
    }

    /**
     * Get expiration
     *
     * @return \DateTime 
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return Announcement
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @Serial\VirtualProperty
     * @Serial\SerializedName("status")
     */
    public function getStatus() {
        if ($this->getExpiration() <= new \DateTime() ) {
            return "expired";
        } else if ($this->getTimestamp() > new \DateTime()) {
            return "hidden";
        } else {
            return "showing";
        }
    }
}