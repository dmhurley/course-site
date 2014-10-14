<?php

namespace Bio\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Bio\UserBundle\Entity\AbstractUserStudent;
use Bio\InfoBundle\Entity\Section;


/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity("username")
 */
class User extends AbstractUserStudent
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
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /****** STUDENT FUNCTIONS ********/
    // in order for a user to impersonate a student,
    // they have to have all the same getter functions
    public function getSid() {
        $sid = ''.$this->getId();
        if (strlen($sid) > 7) {
            $sid = substr($sid, 0, 7);
        } else {
            while (strLen($sid) < 7) {
                $sid = '0'.$sid;
            }
        }
        return $sid;
    }
    public function getFName() {
        return $this->getUsername();
    }
    public function getMName() {
        return '';
    }
    public function getLName() {
        return 'Admin';
    }
    public function getSection() {
        $section = new Section();
        $section->setName('A9')
            ->setStart(new \DateTime('midnight'))
            ->setEnd(new \DateTime('midnight'))
            ->setBldg("HCK\tHitchcock Hall")
            ->setRoom(0);
        return $section;
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
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
     * Set roles
     *
     * @param array $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    
        return $this;
    }

    /**
     * Get roles
     *
     * @return array 
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials() {
        
    }
}