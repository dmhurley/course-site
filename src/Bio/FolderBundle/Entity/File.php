<?php

namespace Bio\FolderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Bio\DataBundle\Exception\BioException;
use Bio\FolderBundle\Entity\FileBase;

/**
 * File
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class File extends FileBase
{

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=1024)
     */
    private $path;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="files")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="mimetype", type="string", length=255)
     */
    private $mime;

    // are not persisted!
    /**
     * @Assert\File()
     */
    private $file;
    private $temp;

    /**
     * Set name
     *
     * @param string $name
     * @return File
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
     * Set path
     *
     * @param string $path
     * @return File
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set parent
     *
     * @param \Bio\FolderBundle\Entity\Folder $parent
     * @return File
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
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {   
        $this->file = $file;
        $this->mime = $file->getMimeType();
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath() {
        return $this->getUploadRootDirectory().'/'.$this->path;
    }

    public function getWebPath() {
        return $this->getUploadDirectory().'/'.$this->path;
    }

    private function getUploadRootDirectory() {
        return __DIR__.'/../../../../web/'.$this->getUploadDirectory();
    }

    private function getUploadDirectory() {
        return 'files';
    }

    /**
     * @ORM\PrePersist()
     */
    public function preUpload() {
        if ($this->getFile() !== null) {
            $extension = $this->getFile()->getClientOriginalExtension();
            var_dump($this->getFile());
            $extension = $extension === '' ? '' : '.'.$extension;
            $name = preg_replace('/[ \t]/', '_', $this->name).$extension;
            $this->temp = $this->path;
            $this->path = $name;

            if (file_exists($this->getAbsolutePath())) {
                throw new BioException("2");
            }
        } else {
            throw new BioException("3");
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->getFile()) {
            return;
        }

        // moves file to app/files directory
        $this->getFile()->move($this->getUploadRootDirectory(), $this->path);
        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if ($file = $this->getAbsolutePath()) {
            if(file_exists($file)){
                unlink($file);
            }
        }
    }

    /**
     * Set mime
     *
     * @param string $mime
     * @return File
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    
        return $this;
    }

    /**
     * Get mime
     *
     * @return string 
     */
    public function getMime()
    {
        return $this->mime;
    }
}