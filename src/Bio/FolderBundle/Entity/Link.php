<?php

namespace Bio\FolderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints as Assert;
use Bio\FolderBundle\Entity\FileBase;


/**
 * Link
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Link extends FileBase
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
     * @ORM\Column(name="address", type="string", length=2048)
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"http", "https", "ftp"}, message="Invalid URL. Remember URLs must start with http://");
     */
    private $address;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="links")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * Set address
     *
     * @param string $address
     * @return Link
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set parent
     *
     * @param \Bio\FolderBundle\Entity\Folder $parent
     * @return Link
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
     * Set name
     *
     * @param string $name
     * @return Link
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
}