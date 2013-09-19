<?php

namespace Bio\FolderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\FolderBundle\Entity\FileBase;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Folder
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Folder extends FileBase
{

    protected $order = "10";

    /**
     * @ORM\OneToMany(targetEntity="FileBase", mappedBy="parent", cascade={"remove", "persist", "refresh"}, fetch="LAZY")
     * @ORM\OrderBy({"order" = "ASC", "name" = "ASC"})
     */
    private $children;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="private", type="boolean")
     */
    private $private;

    /**
     * @ORM\ManyToOne(targetEntity="\Bio\StudentBundle\Entity\Student")
     * @ORM\JoinColumn(name="studentID", referencedColumnName="id", onDelete="CASCADE")
     **/ 
    private $student;

    /**
     * Constructor
     */
    public function __construct()
    {   
        $this->private = false;
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set private
     *
     * @param boolean $private
     * @return Folder
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    
        return $this;
    }

    /**
     * Get private
     *
     * @return boolean 
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * Add links
     *
     * @param \Bio\FolderBundle\Entity\FileBase $child
     * @return Folder
     */
    public function addChild(\Bio\FolderBundle\Entity\FileBase $child)
    {
        $this->children[] = $child;
    
        return $this;
    }

    /**
     * Remove links
     *
     * @param \Bio\FolderBundle\Entity\FileBase $child
     */
    public function removeChild(\Bio\FolderBundle\Entity\FileBase $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get links
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function setStudent(\Bio\StudentBundle\Entity\Student $student) {
        $this->student = $student;

        return $this;
    }

    public function getStudent() {
        return $this->student;
    }
}