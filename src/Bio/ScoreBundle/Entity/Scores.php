<?php

namespace Bio\ScoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Scores
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Scores
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
     * @var privatestring
     *
     * @ORM\Column(name="sid", type="privatestring")
     */
    private $sid;

    /**
     * @var array
     *
     * @ORM\Column(name="scores", type="array")
     */
    private $scores;

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
     * Set sid
     *
     * @param privatestring $sid
     * @return Scores
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    
        return $this;
    }

    /**
     * Get sid
     *
     * @return privatestring 
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set scores
     *
     * @param array $scores
     * @return Scores
     */
    public function setScores($scores)
    {
        $this->scores = $scores;
    
        return $this;
    }

    /**
     * Get scores
     *
     * @return array 
     */
    public function getScores()
    {
        return $this->scores;
    }
}
