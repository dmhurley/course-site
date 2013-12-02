<?php

namespace Bio\StudentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Bio\UserBundle\Entity\AbstractUserStudent;
use JMS\Serializer\Annotation as Serial;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Student
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity(fields={"sid"}, message="There is already a person with that Student ID")
 */
class Student extends AbstractUserStudent
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sid", type="privatestring", unique=true)
     * @Assert\NotBlank()
     * @Assert\Regex("/^[0-9]{7}$/")
     */
    protected $sid;

    /**
     * @var string
     *
     * @ORM\Column(name="fName", type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $fName;

    /**
     * @var string
     *
     * @ORM\Column(name="mName", type="string", length=50, nullable=true)
     */
    protected $mName;

    /**
     * @var string
     *
     * @ORM\Column(name="lName", type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $lName;

    /**
     * @ORM\ManyToOne(targetEntity="Bio\InfoBundle\Entity\Section")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
     */
    protected $section;



    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string")
     * @Serial\Exclude
     */
    protected $password;

    /***** Implementing UserInterface ****/
    public function getUsername() {
        return $this->sid;
    }
    public function getRoles() {
        return array('ROLE_STUDENT');
    }
    public function eraseCredentials() {
        // do nothing
    }
    /***************************************/

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
     * Set sid
     *
     * @param string $sid
     * @return Student
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    
        return $this;
    }

    /**
     * Get sid
     *
     * @return string 
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set fName
     *
     * @param string $fName
     * @return Student
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
        return ucfirst($this->fName);
    }

    /**
     * Set mName
     *
     * @param string $mName
     * @return Student
     */
    public function setMName($mName)
    {
        $this->mName = $mName;
    
        return $this;
    }

    /**
     * Get mName
     *
     * @return string 
     */
    public function getMName()
    {
        return ucfirst($this->mName);
    }


    /**
     * Set lName
     *
     * @param string $lName
     * @return Student
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
        return ucfirst($this->lName);
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Student
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {   
        return $this->password;
    }

    /**
     * Set section
     *
     * @param \Bio\InfoBundle\Entity\Section $section
     * @return Student
     */
    public function setSection(\Bio\InfoBundle\Entity\Section $section = null)
    {
        $this->section = $section;
    
        return $this;
    }

    /**
     * Get section
     *
     * @return \Bio\InfoBundle\Entity\Section 
     */
    public function getSection()
    {
        return $this->section;
    }
}