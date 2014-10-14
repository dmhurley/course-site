<?php

namespace Bio\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"user" = "User", "student" = "Bio\StudentBundle\Entity\Student"})
 */
abstract class AbstractUserStudent implements UserInterface
{	
	/**
	 * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="salt", type="string")
	 */
	protected $salt;

	/**
     * @var string
     *
     * @ORM\Column(name="email", type="privatestring")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

	public function __construct() {
        $this->salt = md5(uniqid(rand(), true));
    }

	public abstract function getUsername();
	public function getSalt() {
		return $this->salt;
	}
	public abstract function getPassword();
	public abstract function getRoles();
	public abstract function eraseCredentials();

	public abstract function getSid();
    public abstract function getFName();
    public abstract function getLName();
	public abstract function getMName();
    public abstract function getSection();

    public function getId() {
    	return $this->id;
    }

    /**
     * Set email
     *
     * @param privatestring $email
     * @return AbstractUserStudent
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return AbstractUserStudent
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return privatestring 
     */
    public function getEmail()
    {
        return $this->email;
    }
}