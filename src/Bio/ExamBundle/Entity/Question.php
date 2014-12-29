<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Question
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Question
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
     * @ORM\Column(name="question", type="text")
     * @Assert\NotBlank()
     */
    private $question;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="text")
     * @Assert\NotBlank()
     */
    private $answer;

    /**
     * @var integer
     *
     * @ORM\Column(name="points", type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $points;

    /**
     * @var array
     *
     * @ORM\Column(name="tags", type="array")
     * @Assert\NotNull()
     */
    private $tags;

    public function __construct() {
        $this->points = 2;
        $this->tags = array();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * Set question
     *
     * @param string $question
     * @return Question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return string 
     */
    public function getQuestion()
    {
        return $this->question;
    }

    public function getFormattedQuestion() {
        $string = $this->question;
        $string = strip_tags($string);
        $string = str_replace("&nbsp;", '', $string);
        return $string;
    }

    /**
     * Set answer
     *
     * @param string $answer
     * @return Question
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
     * @param integer $points
     * @return Question
     */
    public function setPoints($points)
    {
        $this->points = $points;
    
        return $this;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set tags
     *
     * @param array $tags
     * @return Question
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    
        return $this;
    }

    /**
     * Get tags
     *
     * @return array 
     */
    public function getTags()
    {
        return $this->tags;
    }
}