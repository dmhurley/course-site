<?php

namespace Bio\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
}
