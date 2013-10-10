<?php

namespace Bio\ExamBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use JMS\Serializer\Annotation as Serial;

/**
 * Exam
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Assert\Callback(methods={"tStartBeforeTEnd"})
 * @Assert\Callback(methods={"tDurationLongEnough"})
 * @Assert\Callback(methods={"gStartBeforeGEnd"})
 * @Assert\Callback(methods={"gDurationLongEnough"})
 * @Assert\Callback(methods={"otherChecks"})
 */
class Exam
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
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="section", type="string", length=2, nullable=true)
     * @Assert\Regex("/(\A\Z)|(^[A-Z][A-Z0-9]?$)/")
     */
    private $section;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tdate", type="date")
     * @Assert\Date()
     * @Serial\Type("DateTime<'U'>")
     */
    private $tDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tstart", type="time")
     * @Assert\Time()
     * @Serial\Type("DateTime<'U'>")
     */
    private $tStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tend", type="time")
     * @Assert\Time()
     * @Serial\Type("DateTime<'U'>")
     */
    private $tEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="tduration", type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $tDuration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gdate", type="date")
     * @Assert\Date()
     * @Serial\Type("DateTime<'U'>")
     */
    private $gDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gstart", type="time")
     * @Assert\Time()
     * @Serial\Type("DateTime<'U'>")
     */
    private $gStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="gend", type="time")
     * @Assert\Time()
     * @Serial\Type("DateTime<'U'>")
     */
    private $gEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="gDuration", type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value=0)
     */
    private $gDuration;

    /**
     * @ORM\ManyToMany(targetEntity="Question")
     * @ORM\JoinTable(name="exam_questions",
     *      joinColumns={@ORM\JoinColumn(name="test_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="CASCADE")})
     * @Serial\MaxDepth(1)
     */
    private $questions;

    public function __construct() {
        $this->tDate = new \DateTime();
        $this->gDate = new \DateTime();
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Exam
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add questions
     *
     * @param \Bio\ExamBundle\Entity\Question $questions
     * @return Exam
     */
    public function addQuestion(\Bio\ExamBundle\Entity\Question $questions)
    {
        $this->questions[] = $questions;
    
        return $this;
    }

    /**
     * Remove questions
     *
     * @param \Bio\ExamBundle\Entity\Question $questions
     */
    public function removeQuestion(\Bio\ExamBundle\Entity\Question $questions)
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

    public function setQuestions($questions) {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($questions as $question) {
            $this->addQuestion($question);
        }

        return $this;
    }

    /**
     * Set tDate
     *
     * @param \DateTime $tDate
     * @return Exam
     */
    public function setTDate($tDate)
    {
        $this->tDate = $tDate;
    
        return $this;
    }

    /**
     * Get tDate
     *
     * @return \DateTime 
     */
    public function getTDate()
    {
        return $this->tDate;
    }

    /**
     * Set tStart
     *
     * @param \DateTime $tStart
     * @return Exam
     */
    public function setTStart($tStart)
    {
        $this->tStart = $tStart;
    
        return $this;
    }

    /**
     * Get tStart
     *
     * @return \DateTime 
     */
    public function getTStart()
    {
        return $this->tStart;
    }

    /**
     * Set tEnd
     *
     * @param \DateTime $tEnd
     * @return Exam
     */
    public function setTEnd($tEnd)
    {
        $this->tEnd = $tEnd;
    
        return $this;
    }

    /**
     * Get tEnd
     *
     * @return \DateTime 
     */
    public function getTEnd()
    {
        return $this->tEnd;
    }

    /**
     * Set tDuration
     *
     * @param integer $tDuration
     * @return Exam
     */
    public function setTDuration($tDuration)
    {
        $this->tDuration = $tDuration;
    
        return $this;
    }

    /**
     * Get tDuration
     *
     * @return integer 
     */
    public function getTDuration()
    {
        return $this->tDuration;
    }

    /**
     * Set gDate
     *
     * @param \DateTime $gDate
     * @return Exam
     */
    public function setGDate($gDate)
    {
        $this->gDate = $gDate;
    
        return $this;
    }

    /**
     * Get gDate
     *
     * @return \DateTime 
     */
    public function getGDate()
    {
        return $this->gDate;
    }

    /**
     * Set gStart
     *
     * @param \DateTime $gStart
     * @return Exam
     */
    public function setGStart($gStart)
    {
        $this->gStart = $gStart;
    
        return $this;
    }

    /**
     * Get gStart
     *
     * @return \DateTime 
     */
    public function getGStart()
    {
        return $this->gStart;
    }

    /**
     * Set gEnd
     *
     * @param \DateTime $gEnd
     * @return Exam
     */
    public function setGEnd($gEnd)
    {
        $this->gEnd = $gEnd;
    
        return $this;
    }

    /**
     * Get gEnd
     *
     * @return \DateTime 
     */
    public function getGEnd()
    {
        return $this->gEnd;
    }

    /**
     * Set gDuration
     *
     * @param integer $gDuration
     * @return Exam
     */
    public function setGDuration($gDuration)
    {
        $this->gDuration = $gDuration;
    
        return $this;
    }

    /**
     * Get gDuration
     *
     * @return integer 
     */
    public function getGDuration()
    {
        return $this->gDuration;
    }

    /**
     * Set section
     *
     * @param string $section
     * @return Exam
     */
    public function setSection($section)
    {
        $this->section = $section;
    
        return $this;
    }

    /**
     * Get section
     *
     * @return string 
     */
    public function getSection()
    {
        return $this->section;
    }

    public function tStartBeforeTEnd(ExecutionContextInterface $context) {
        if ($this->tStart >= $this->tEnd) {
            $context->addViolationAt('tEnd', 'Exam end cannot be before exam start.');
        }
    }

    public function tDurationLongEnough(ExecutionContextInterface $context) {
        if (($this->tEnd->getTimestamp() - $this->tStart->getTimestamp())/60 < $this->tDuration) {
            $context->addViolationAt('tDuration', 'Exam duration must be shorter than exam window.');
        }
    }

     public function gStartBeforeGEnd(ExecutionContextInterface $context) {
        if ($this->gStart >= $this->gEnd) {
            $context->addViolationAt('gEnd', 'Grade end cannot be before grade start.');
        }
    }

    public function gDurationLongEnough(ExecutionContextInterface $context) {
        if (($this->gEnd->getTimestamp() - $this->gStart->getTimestamp())/60 < $this->gDuration) {
            $context->addViolationAt('gDuration', 'Grade duration must be shorter than grade window.');
        }
    }

    public function otherChecks(ExecutionContextInterface $context) {
        $testStart = new \DateTime($this->getTDate()->format('Y-m-d ').$this->getTStart()->format('H:i:s'));
        $testEnd = new \DateTime($this->getTDate()->format('Y-m-d ').$this->getTEnd()->format('H:i:s'));
        $gradeStart = new \DateTime($this->getGDate()->format('Y-m-d ').$this->getGStart()->format('H:i:s'));
        $gradeEnd = new \DateTime($this->getGDate()->format('Y-m-d ').$this->getGEnd()->format('H:i:s'));

       if ($gradeEnd <= $testEnd) {
           $context->addViolationAt("gEnd", "Grading cannot end before exam ends.");
       }

       if (($gradeEnd->getTimestamp() - $testStart->getTimestamp())/60 < ($this->getTDuration() + $this->getGDuration())) {
            $context->addViolationAt("gEnd", "Total window must be longer than total duration.");
       }
    }
}