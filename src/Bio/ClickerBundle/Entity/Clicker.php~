<?php

namespace Bio\ClickerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\StudentBundle\Entity\Student as Student;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Clicker
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Clicker
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
     * @ORM\Column(name="cid", type="string", length=255, unique=true)
     * @Assert\Regex("/^[0-9A-F]{6}$/")
     */
    private $cid;

    /**
     * @var string
     *
     * @ORM\Column(name="sid", type="string", length=255, unique=true)
     * @Assert\Regex("/^[0-9]{7}$/")
     */
    private $sid;


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
     * Set cid
     *
     * @param string $cid
     * @return Clicker
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    
        return $this;
    }

    /**
     * Get cid
     *
     * @return string 
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * Set sid
     *
     * @param string $sid
     * @return Clicker
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    
        return $this;
    }

    /**
     * Get sid
     *
     * @return string 
     */
    public function getSid()
    {
        return $this->sid;
    }


}