<?php

namespace Bio\TripBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Response
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Response
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
     * @ORM\ManyToOne(targetEntity="EvalQuestion")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     */
    private $evalQuestion;


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
     * @return Response
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

    /**
     * Set evalQuestion
     *
     * @param \Bio\TripBundle\Entity\EvalQuestion $evalQuestion
     * @return Response
     */
    public function setEvalQuestion(\Bio\TripBundle\Entity\EvalQuestion $evalQuestion = null)
    {
        $this->evalQuestion = $evalQuestion;
    
        return $this;
    }

    /**
     * Get evalQuestion
     *
     * @return \Bio\TripBundle\Entity\EvalQuestion 
     */
    public function getEvalQuestion()
    {
        return $this->evalQuestion;
    }
}