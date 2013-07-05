<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Person
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Person extends Base
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
     * @ORM\Column(name="fName", type="string", length=255)
     */
    private $fName;

    /**
     * @var string
     *
     * @ORM\Column(name="lName", type="string", length=255)
     */
    private $lName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="bldg", type="string", length=255)
     */
    private $bldg;

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255)
     */
    private $room;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="Hours", mappedBy="person", cascade={"remove"})
     */
    private $hours;

    public function __construct() {
        $this->products = new ArrayCollection();
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
     * Set fName
     *
     * @param string $fName
     * @return Person
     */
    public function setFName($fName)
    {
        $this->fName = $fName;
    
        return $this;
    }

    /**
     * Get fName
     *
     * @return string 
     */
    public function getFName()
    {
        return $this->fName;
    }

    /**
     * Set lName
     *
     * @param string $lName
     * @return Person
     */
    public function setLName($lName)
    {
        $this->lName = $lName;
    
        return $this;
    }

    /**
     * Get lName
     *
     * @return string 
     */
    public function getLName()
    {
        return $this->lName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Person
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set bldg
     *
     * @param string $bldg
     * @return Person
     */
    public function setBldg($bldg)
    {
        $this->bldg = $bldg;
    
        return $this;
    }

    /**
     * Get bldg
     *
     * @return string 
     */
    public function getBldg()
    {
        return $this->bldg;
    }

    /**
     * Set room
     *
     * @param string $room
     * @return Person
     */
    public function setRoom($room)
    {
        $this->room = $room;
    
        return $this;
    }

    /**
     * Get room
     *
     * @return string 
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Person
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

    public function addToForm(FormBuilder $builder) {
        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
        $builder->add('fName', 'text', array('label' => 'First Name:'))
            ->add('lName', 'text', array('label' => 'Last Name:'))
            ->add('email', 'email', array('label' => 'Email:'))
            ->add('bldg', 'choice', array('choices' => array_combine($array, $array), 'validation_groups' => false, 'label' => 'Building:'))
            ->add('room', 'text', array('label' => 'Room:'))
            ->add('title', 'choice', array('choices' => array('instructor' => 'Instructor', 'ta' => 'TA', 'coordinator' => 'Coordinator'), 'label' => 'Title:'));
        return $builder;
    }

    public function setAll($entity) {
        $this->setFName($entity->getFName())
            ->setLName($entity->getLName())
            ->setEmail($entity->getEmail())
            ->setBldg($entity->getBldg())
            ->setRoom($entity->getRoom())
            ->setTitle($entity->getTitle());

        return $this;
    }

    /**
     * Add hours
     *
     * @param \Bio\InfoBundle\Entity\Hours $hours
     * @return Person
     */
    public function addHour(\Bio\InfoBundle\Entity\Hours $hours)
    {
        $this->hours[] = $hours;
    
        return $this;
    }

    /**
     * Remove hours
     *
     * @param \Bio\InfoBundle\Entity\Hours $hours
     */
    public function removeHour(\Bio\InfoBundle\Entity\Hours $hours)
    {
        $this->hours->removeElement($hours);
    }

    /**
     * Get hours
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHours()
    {
        return $this->hours;
    }
}