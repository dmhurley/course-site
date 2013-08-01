<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Answer
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
     * @var array
     *
     * @ORM\Column(name="points", type="array")
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity="TestTaker", inversedBy="answers")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $testTaker;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumn(name="questionID", referencedColumnName="id", onDelete="CASCADE")
     **/
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
     * @return Answer
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
     * Set points
     *
     * @param array $points
     * @return Answer
     */
    public function setPoints($points)
    {
        $this->points = $points;
    
        return $this;
    }

    public function addPoint($point) {
        $this->points[] = $point;
    }

    /**
     * Get points
     *
     * @return array 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set testTaker
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $testTaker
     * @return Answer
     */
    public function setTestTaker(\Bio\ExamBundle\Entity\TestTaker $testTaker = null)
    {
        $this->testTaker = $testTaker;
    
        return $this;
    }

    /**
     * Get testTaker
     *
     * @return \Bio\ExamBundle\Entity\TestTaker 
     */
    public function getTestTaker()
    {
        return $this->testTaker;
    }

    /**
     * Set question
     *
     * @param \Bio\ExamBundle\Entity\Question $question
     * @return Answer
     */
    public function setQuestion(\Bio\ExamBundle\Entity\Question $question = null)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return \Bio\ExamBundle\Entity\Question 
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
