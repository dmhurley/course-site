<?php

namespace Bio\ScoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stat
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Stat
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
     * @ORM\Column(name="name", type="string")
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="stats", type="array")
     */
    private $stats;


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
     * Set stats
     *
     * @param array $stats
     * @return Stat
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    
        return $this;
    }

    /**
     * Get stats
     *
     * @return array 
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Stat
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