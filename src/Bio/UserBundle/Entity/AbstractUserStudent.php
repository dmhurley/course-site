<?php

namespace Bio\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

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

	public abstract function getUsername();
	public abstract function getSalt();
	public abstract function getPassword();
	public abstract function getRoles();
	public abstract function eraseCredentials();

	public abstract function getSid();
    public abstract function getFName();
    public abstract function getLName();
    public abstract function getSection();
    public abstract function getEmail();

    public function getId() {
    	return $this->id;
    }
}