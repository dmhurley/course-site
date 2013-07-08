<?php

namespace Bio\FolderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Folder
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Folder
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent", cascade={"remove", "persist", "refresh"}, fetch="EAGER")
     */
    private $folders;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="parent", cascade={"remove", "persist", "refresh"}, fetch="EAGER")
     */
    private $files;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="folders")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;


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
     * Set name
     *
     * @param string $name
     * @return Folder
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->folders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set parent
     *
     * @param \Bio\FolderBundle\Entity\Folder $parent
     * @return Folder
     */
    public function setParent(\Bio\FolderBundle\Entity\Folder $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Bio\FolderBundle\Entity\Folder 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add folders
     *
     * @param \Bio\FolderBundle\Entity\Folder $folders
     * @return Folder
     */
    public function addFolder(\Bio\FolderBundle\Entity\Folder $folders)
    {
        $this->folders[] = $folders;
    
        return $this;
    }

    /**
     * Remove folders
     *
     * @param \Bio\FolderBundle\Entity\Folder $folders
     */
    public function removeFolder(\Bio\FolderBundle\Entity\Folder $folders)
    {
        $this->folders->removeElement($folders);
    }

    /**
     * Get folders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFolders()
    {
        return $this->folders;
    }

    /**
     * Add files
     *
     * @param \Bio\FolderBundle\Entity\File $files
     * @return Folder
     */
    public function addFile(\Bio\FolderBundle\Entity\File $files)
    {
        $this->files[] = $files;
    
        return $this;
    }

    /**
     * Remove files
     *
     * @param \Bio\FolderBundle\Entity\File $files
     */
    public function removeFile(\Bio\FolderBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }
}