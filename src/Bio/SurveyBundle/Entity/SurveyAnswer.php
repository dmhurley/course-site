<?php

namespace Bio\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SurveyAnswer
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SurveyAnswer
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
     * @ORM\Column(name="answer", type="text")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $answer;

    /**
     * @ORM\ManyToOne(targetEntity="SurveyTaker", inversedBy="answers")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $surveyTaker;

    /**
     * @ORM\ManyToOne(targetEntity="SurveyQuestion")
     * @ORM\JoinColumn(name="questionID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $question;

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
     * Set answer
     *
     * @param string $answer
     * @return SurveyAnswer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return string
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    public function getAnswerText() {
        $data = $this->getQuestion()->getData();

        if (count($data) > 1) {
            return $data[$this->getAnswer() + 1];
        } else {
            return $this->getAnswer();
        }
    }

    /**
     * Set surveyTaker
     *
     * @param \Bio\SurveyBundle\Entity\SurveyTaker $surveyTaker
     * @return SurveyAnswer
     */
    public function setSurveyTaker(\Bio\SurveyBundle\Entity\SurveyTaker $surveyTaker = null)
    {
        $this->surveyTaker = $surveyTaker;

        return $this;
    }

    /**
     * Get surveyTaker
     *
     * @return \Bio\SurveyBundle\Entity\SurveyTaker
     */
    public function getSurveyTaker()
    {
        return $this->surveyTaker;
    }

    /**
     * Set question
     *
     * @param \Bio\SurveyBundle\Entity\SurveyQuestion $question
     * @return SurveyAnswer
     */
    public function setQuestion(\Bio\SurveyBundle\Entity\SurveyQuestion $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \Bio\SurveyBundle\Entity\SurveyQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
