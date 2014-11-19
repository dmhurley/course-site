<?php

namespace Bio\SurveyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SurveyTaker
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SurveyTaker
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
     * @ORM\ManyToOne(targetEntity="\Bio\UserBundle\Entity\AbstractUserStudent")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $student;

    /**
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $survey;

    /**
     * @ORM\OneToMany(targetEntity="SurveyAnswer", mappedBy="surveyTaker", cascade={"remove"})
     */
    private $answers;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
