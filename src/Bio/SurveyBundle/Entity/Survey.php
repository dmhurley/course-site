<?php

namespace Bio\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * Survey
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Bio\SurveyBundle\Repository\SurveyRepository")
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
     * @var boolean
     * @ORM\Column(name="anonymous", type="boolean")
     * @Assert\NotNull()
     */
    private $anonymous;

    /**
     * @ORM\OneToMany(targetEntity="SurveyQuestion", mappedBy="survey", cascade={"persist"})
     * @Assert\Count(min=1, minMessage="You must have at least one question.")
     */
    private $questions;

    /**
     * @ORM\OneToMany(targetEntity="SurveyTaker", mappedBy="survey")
     */
    private $takers;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hidden", type="boolean")
     * @Assert\Type(type="bool", message="The value must be true or false.")
     */
    private $hidden;

    public function __construct() {
        $this->questions = new ArrayCollection();
        $this->takers = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function getAnonymous() {
        return $this->anonymous;
    }

    public function setAnonymous($anonymous) {
        $this->anonymous = $anonymous;

        return $this;
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

    /**
    * Add takers
    *
    * @param \Bio\SurveyBundle\Entity\SurveyQuestion $takers
    * @return Survey
    */
    public function addTaker(\Bio\SurveyBundle\Entity\SurveyTaker $takers)
    {
        $this->takers[] = $takers;

        return $this;
    }

    /**
    * Remove takers
    *
    * @param \Bio\SurveyBundle\Entity\SurveyTaker $takers
    */
    public function removeTaker(\Bio\SurveyBundle\Entity\SurveyTaker $takers)
    {
        $this->takers->removeElement($takers);
    }

    /**
    * Get takers
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getTakers()
    {
        return $this->takers;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     * @return Survey
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return $this->hidden;
    }
}
