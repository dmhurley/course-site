<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serial;

/**
 * Grade
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Grade
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
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var integer
     *
     * @ORM\Column(name="points", type="integer", nullable=true)
     */
    private $points;

    /**
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Answer", inversedBy="grades")
     * @ORM\JoinColumn(name="answer_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serial\MaxDepth(1)
     */
    private $answer;

    /**
     * @ORM\ManyToOne(targetEntity="TestTaker")
     * @ORM\JoinColumn(name="grader_id", referencedColumnName="id", onDelete="CASCADE")
     * @Serial\MaxDepth(1)
     **/
    private $grader;

    public function __construct() {
        $this->start = new \DateTime();
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
     * Set start
     *
     * @param \DateTime $start
     * @return Grade
     */
    public function setStart($start)
    {
        $this->start = $start;
    
        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Grade
     */
    public function setEnd($end)
    {
        $this->end = $end;
    
        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set points
     *
     * @param integer $points
     * @return Grade
     */
    public function setPoints($points)
    {
        $this->points = $points;
        $this->end = new \Datetime();
    
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
     * Set answer
     *
     * @param \Bio\ExamBundle\Entity\Answer $answer
     * @return Grade
     */
    public function setAnswer(\Bio\ExamBundle\Entity\Answer $answer = null)
    {
        $this->answer = $answer;
    
        return $this;
    }

    /**
     * Get answer
     *
     * @return \Bio\ExamBundle\Entity\Answer 
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set grader
     *
     * @param \Bio\ExamBundle\Entity\TestTaker $grader
     * @return Grade
     */
    public function setGrader(\Bio\ExamBundle\Entity\TestTaker $grader = null)
    {
        $this->grader = $grader;
    
        return $this;
    }

    /**
     * Get grader
     *
     * @return \Bio\ExamBundle\Entity\TestTaker 
     */
    public function getGrader()
    {
        return $this->grader;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Grade
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }
}