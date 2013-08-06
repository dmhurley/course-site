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
     * @ORM\ManyToOne(targetEntity="Query")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
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
     * @return Response
     */
    public function setResponse($answer)
    {
        $this->answer = $answer;
    
        return $this;
    }

    /**
     * Get answer
     *
     * @return string 
     */
    public function getResponse()
    {
        return $this->answer;
    }

    /**
     * Set question
     *
     * @param \Bio\TripBundle\Entity\Query $question
     * @return Response
     */
    public function setQuery(\Bio\TripBundle\Entity\Query $question = null)
    {
        $this->question = $question;
    
        return $this;
    }

    /**
     * Get question
     *
     * @return \Bio\TripBundle\Entity\Query 
     */
    public function getQuery()
    {
        return $this->question;
    }
}