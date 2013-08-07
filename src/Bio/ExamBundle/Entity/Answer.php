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
     * @ORM\OneToMany(targetEntity="Grade", mappedBy="answer", cascade={"remove"})
     */
    private $points;

    /**
     * @ORM\ManyToOne(targetEntity="TestTaker", inversedBy="answers")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $testTaker;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumn(name="questionID", referencedColumnName="id", onDelete="CASCADE")
     **/
    private $question;

    public function __construct() {
        $this->points = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Remove points
     *
     * @param \Bio\ExamBundle\Entity\Grade $points
     */
    public function removePoint(\Bio\ExamBundle\Entity\Grade $points)
    {
        $this->points->removeElement($points);
    }

    /**
     * Add points
     *
     * @param \Bio\ExamBundle\Entity\Grade $points
     * @return Answer
     */
    public function addPoint(\Bio\ExamBundle\Entity\Grade $points)
    {
        $this->points[] = $points;
    
        return $this;
    }

    /**
     * Get points
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPoints()
    {
        return $this->points;
    }

    public function grade($grader, $point) {
        foreach($this->points as $grade) {
            if ($grade->getGrader() === $grader) {
                $grade->setPoints($point);
                break;
            }
        }
    }

    public function isGraded() {
        foreach($this->points as $grade) {
            if ($grade->getPoints() !== null) {
                return true;
            }
        }
        return false;
    }
}