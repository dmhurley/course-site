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

    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent", cascade={"remove", "persist", "refresh"}, fetch="LAZY")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $folders;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="parent", cascade={"remove", "persist", "refresh"}, fetch="LAZY")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity="Link", mappedBy="parent", cascade={"remove", "persist", "refresh"}, fetch="LAZY")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $links;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="private", type="boolean")
     */
    private $private;

    public function __contruct() {
        $this->private = false;
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
     * @param \Bio\FolderBundle\Entity\Link $links
     * @return Folder
     */
    public function addLink(\Bio\FolderBundle\Entity\Link $links)
    {
        $this->links[] = $links;
    
        return $this;
    }

    /**
     * Remove links
     *
     * @param \Bio\FolderBundle\Entity\Link $links
     */
    public function removeLink(\Bio\FolderBundle\Entity\Link $links)
    {
        $this->links->removeElement($links);
    }

    /**
     * Get links
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLinks()
    {
        return $this->links;
    }
}