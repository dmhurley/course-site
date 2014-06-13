<?php

namespace Bio\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * Survey
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Survey
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="SurveyQuestion", mappedBy="survey")
     */
    private $questions;

    public function __construct() {
        $this->questions = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;
        return this;
    }

    public function getName() {
        return $name;
    }

    /**
     * Add questions
     *
     * @param \Bio\SurveyBundle\Entity\SurveyQuestion $questions
     * @return Survey
     */
    public function addQuestion(\Bio\SurveyBundle\Entity\SurveyQuestion $questions)
    {
        $this->questions[] = $questions;
    
        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Bio\SurveyBundle\Entity\SurveyQuestion $questions
     */
    public function removeQuestion(\Bio\SurveyBundle\Entity\SurveyQuestion $questions)
    {
        $this->questions->removeElement($questions);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestions()
    {
        return $this->questions;
    }
}