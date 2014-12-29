<?php

namespace Bio\TripBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TripGlobal
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class TripGlobal
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
     * @ORM\Column(name="opening", type="datetime")
     * @Assert\DateTime()
     */
    private $opening;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closing", type="datetime")
     * @Assert\DateTime()
     */
    private $closing;

    /**
     * @var integer
     *
     * @ORM\Column(name="maxTrips", type="integer")
     * @Assert\GreaterThan(value=0)
     * @Assert\NotBlank()
     */
    private $maxTrips;

    /**
     * @var integer
     *
     * @ORM\Column(name="evalDue", type="integer")
     * @Assert\GreaterThan(value=1)
     * @Assert\NotBlank()
     */
    private $evalDue;

    /**
     * @var string
     *
     * @ORM\Column(name="guidePass", type="privatestring")
     * @Assert\NotBlank()
     */
    private $guidePass;

    /**
     * @var string
     *
     * @ORM\Column(name="instructions", type="text")
     * @Assert\NotNull()
     */
    private $instructions;

    /**
     * @var string
     *
     * @ORM\Column(name="promo", type="text")
     * @Assert\NotNull()
     */
    private $promo;

    /**
     * @ORM\ManyToMany(targetEntity="EvalQuestion")
     * @ORM\JoinTable(name="default_questions",
     *      joinColumns={@ORM\JoinColumn(name="global", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="query_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *     )
     */
    private $evalQuestions;

    /**
     * @var boolean
     *
     * @ORM\Column(name="notifications", type="boolean")
     * @Assert\Type(type="boolean")
     */
    private $notifications;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="datetime")
     * @Assert\DateTime()
     * @Assert\NotBlank()
     */
    private $start;

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
     * Set signupStart
     *
     * @param \DateTime $signupStart
     * @return TripGlobal
     */
    public function setSignupStart($signupStart)
    {
        $this->signupStart = $signupStart;
    
        return $this;
    }

    /**
     * Get signupStart
     *
     * @return \DateTime 
     */
    public function getSignupStart()
    {
        return $this->signupStart;
    }

    /**
     * Set evaluationDeadline
     *
     * @param \DateTime $evaluationDeadline
     * @return TripGlobal
     */
    public function setEvaluationDeadline($evaluationDeadline)
    {
        $this->evaluationDeadline = $evaluationDeadline;
    
        return $this;
    }

    /**
     * Get evaluationDeadline
     *
     * @return \DateTime 
     */
    public function getEvaluationDeadline()
    {
        return $this->evaluationDeadline;
    }

    /**
     * Set opening
     *
     * @param \DateTime $opening
     * @return TripGlobal
     */
    public function setOpening($opening)
    {
        $this->opening = $opening;
    
        return $this;
    }

    /**
     * Get opening
     *
     * @return \DateTime 
     */
    public function getOpening()
    {
        return $this->opening;
    }

    /**
     * Set closing
     *
     * @param \DateTime $closing
     * @return TripGlobal
     */
    public function setClosing($closing)
    {
        $this->closing = $closing;
    
        return $this;
    }

    /**
     * Get closing
     *
     * @return \DateTime 
     */
    public function getClosing()
    {
        return $this->closing;
    }

    /**
     * Set maxTrips
     *
     * @param integer $maxTrips
     * @return TripGlobal
     */
    public function setMaxTrips($maxTrips)
    {
        $this->maxTrips = $maxTrips;
    
        return $this;
    }

    /**
     * Get maxTrips
     *
     * @return integer 
     */
    public function getMaxTrips()
    {
        return $this->maxTrips;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->evalQuestions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setEvalQuestions(array $questions) {
        $this->evalQuestions = new \Doctrine\Common\Collections\ArrayCollection();
        foreach($questions as $question) {
            $this->addEvalQuestion($question);
        }

        return $this;
    }

    /**
     * Add evalQuestions
     *
     * @param \Bio\TripBundle\Entity\EvalQuestion $evalQuestions
     * @return TripGlobal
     */
    public function addEvalQuestion(\Bio\TripBundle\Entity\EvalQuestion $evalQuestions)
    {
        $this->evalQuestions[] = $evalQuestions;
    
        return $this;
    }

    /**
     * Remove evalQuestions
     *
     * @param \Bio\TripBundle\Entity\EvalQuestion $evalQuestions
     */
    public function removeEvalQuestion(\Bio\TripBundle\Entity\EvalQuestion $evalQuestions)
    {
        $this->evalQuestions->removeElement($evalQuestions);
    }

    /**
     * Get evalQuestions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvalQuestions()
    {
        return $this->evalQuestions;
    }

    /**
     * Set evalDue
     *
     * @param integer $evalDue
     * @return TripGlobal
     */
    public function setEvalDue($evalDue)
    {
        $this->evalDue = $evalDue;
    
        return $this;
    }

    /**
     * Get evalDue
     *
     * @return integer 
     */
    public function getEvalDue()
    {
        return $this->evalDue;
    }

    /**
     * Set guidePass
     *
     * @param privatestring $guidePass
     * @return TripGlobal
     */
    public function setGuidePass($guidePass)
    {
        $this->guidePass = $guidePass;
    
        return $this;
    }

    /**
     * Get guidePass
     *
     * @return privatestring 
     */
    public function getGuidePass()
    {
        return $this->guidePass;
    }

    /**
     * Set promo
     *
     * @param string $promo
     * @return TripGlobal
     */
    public function setPromo($promo)
    {
        $this->promo = $promo;
    
        return $this;
    }

    /**
     * Get promo
     *
     * @return string 
     */
    public function getPromo()
    {
        return $this->promo;
    }

    /**
     * Set instructions
     *
     * @param string $instructions
     * @return TripGlobal
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    
        return $this;
    }

    /**
     * Get instructions
     *
     * @return string 
     */
    public function getInstructions()
    {
        return $this->instructions;
    }
    
     /**
     * Set notifications
     *
     * @param boolean $notifications
     * @return ClickerGlobal
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
    
        return $this;
    }

    /**
     * Get notifications
     *
     * @return boolean 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return ClickerGlobal
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
}