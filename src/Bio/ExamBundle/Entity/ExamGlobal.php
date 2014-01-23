<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ExamGlobal
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ExamGlobal
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
     * @var integer
     * @Assert\GreaterThanOrEqual(value=0)
     * @ORM\Column(name="grade", type="integer")
     */
    private $grade;

    /** 
     * @var boolean
     * 
     * @ORM\Column(name="comments", type="boolean")
     */
    private $comments;

    /**
     * @var integer
     *
     * @Assert\GreaterThanOrEqual(value=0)
     * @ORM\Column(name="review_hours", type="integer")
     */
    private $reviewHours;

    /**
     * @var string
     *
     * @ORM\Column(name="rules", type="text")
     */
    private $rules;


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
     * Set grade
     *
     * @param integer $grade
     * @return ExamGlobal
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
    
        return $this;
    }

    /**
     * Get grade
     *
     * @return integer 
     */
    public function getGrade()
    {
        return $this->grade;
    }

    public function setComments($comments) {
        $this->comments = $comments;

        return $this;
    }

    public function getComments() {
        return $this->comments;
    }

     /**
     * Set reviewHours
     *
     * @param integer $reviewHours
     * @return ExamGlobal
     */
    public function setReviewHours($reviewHours)
    {
        $this->reviewHours = $reviewHours;
    
        return $this;
    }

    /**
     * Get reviewHours
     *
     * @return integer 
     */
    public function getReviewHours()
    {
        return $this->reviewHours;
    }

    /**
     * Set rules
     *
     * @param string $rules
     * @return ExamGlobal
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    
        return $this;
    }

    /**
     * Get rules
     *
     * @return string 
     */
    public function getRules()
    {
        return $this->rules;
    }
}