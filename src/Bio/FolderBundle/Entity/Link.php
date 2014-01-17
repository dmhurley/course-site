<?php

namespace Bio\FolderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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

    protected $order = "30";

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=2048)
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"http", "https", "ftp"}, message="Invalid URL. Remember URLs must start with http://");
     */
    private $address;

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
}