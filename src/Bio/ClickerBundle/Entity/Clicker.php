<?php

namespace Bio\ClickerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Clicker
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Bio\ClickerBundle\Repository\ClickerRepository")
 */
class Clicker
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
     * @ORM\Column(name="cid", type="string", length=255, unique=true)
     * @Assert\Regex( pattern="/^[0-9A-Fa-f]{6}$/", message="6 character clicker ID (0-9 A-F).")
     */
    private $cid;

    /**
     * @ORM\OneToOne(targetEntity="\Bio\UserBundle\Entity\AbstractUserStudent")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     */
    private $student;


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
     * Set cid
     *
     * @param string $cid
     * @return Clicker
     */
    public function setCid($cid)
    {
        $this->cid = strtoupper($cid);

        return $this;
    }

    /**
     * Get cid
     *
     * @return string
     */
    public function getCid()
    {
        return $this->cid;
    }

    public function getSid() {
        return $this->student->getSid();
    }

    /**
     * Set student
     *
     * @param \Bio\UserBundle\Entity\AbstractUserStudent $student
     * @return Clicker
     */
    public function setStudent(\Bio\UserBundle\Entity\AbstractUserStudent $student = null)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \Bio\UserBundle\Entity\AbstractUserStudent
     */
    public function getStudent()
    {
        return $this->student;
    }
}
