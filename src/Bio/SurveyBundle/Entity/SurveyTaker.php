<?php

namespace Bio\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * SurveyTaker
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SurveyTaker
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
     * @ORM\ManyToOne(targetEntity="\Bio\UserBundle\Entity\AbstractUserStudent")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumn(name="surveyID", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $survey;

    /**
     * @ORM\OneToMany(targetEntity="SurveyAnswer", mappedBy="surveyTaker", cascade={"remove", "persist"})
     * @Assert\Valid(traverse=true)
     */
    private $answers;

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
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set student
     *
     * @param \Bio\UserBundle\Entity\AbstractUserStudent $student
     * @return SurveyTaker
     */
    public function setStudent(\Bio\UserBundle\Entity\AbstractUserStudent $student = null)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \Bio\UserBundle\Entity\AbstractUserStudent
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set survey
     *
     * @param \Bio\SurveyBundle\Entity\Survey $survey
     * @return SurveyTaker
     */
    public function setSurvey(\Bio\SurveyBundle\Entity\Survey $survey = null)
    {
        $this->survey = $survey;

        return $this;
    }

    /**
     * Get survey
     *
     * @return \Bio\SurveyBundle\Entity\Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * Add answers
     *
     * @param \Bio\SurveyBundle\Entity\SurveyAnswer $answers
     * @return SurveyTaker
     */
    public function addAnswer(\Bio\SurveyBundle\Entity\SurveyAnswer $answers)
    {
        $this->answers[] = $answers;

        return $this;
    }

    /**
     * Remove answers
     *
     * @param \Bio\SurveyBundle\Entity\SurveyAnswer $answers
     */
    public function removeAnswer(\Bio\SurveyBundle\Entity\SurveyAnswer $answers)
    {
        $this->answers->removeElement($answers);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }
}
