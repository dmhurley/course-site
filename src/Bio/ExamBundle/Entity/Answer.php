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
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumn(name="questionID", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $question;

    /**
     * @ORM\ManyToMany(targetEntity="TestTaker")
     * @ORM\JoinTable(name="answer_graders",
     *      joinColumns={@ORM\JoinColumn(name="answerID", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="takerID", referencedColumnName="id", onDelete="CASCADE")})
     **/
    private $graders;

    /**
     * @ORM\ManyToOne(targetEntity="TestTaker", inversedBy="answers")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $testTaker;

    /**
     * @var array
     *
     * @ORM\Column(name="points", type="array")
     */
    private $points;

    public function __construct() {
        $this->graders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->points = array();
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

    /**
     * Get points
     *
     * @return array 
     */
    public function getPoints()
    {
        return $this->points;
    }

    public function addPoint($point) {
        $this->points[0] = $point;

        return $this;
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

    /**
     * Add graders
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $graders
     * @return Answer
     */
    public function addGrader(\Bio\ExamBundle\Entity\TestTaker $graders)
    {
        $this->graders[] = $graders;
    
        return $this;
    }

    /**
     * Remove graders
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $graders
     */
    public function removeGrader(\Bio\ExamBundle\Entity\TestTaker $graders)
    {
        $this->graders->removeElement($graders);
    }

    /**
     * Get graders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGraders()
    {
        return $this->graders;
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
}