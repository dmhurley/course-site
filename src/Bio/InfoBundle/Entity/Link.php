<?php

namespace Bio\InfoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Bio\InfoBundle\Entity\Base;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Link
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Link extends Base
{

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=2048)
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"http", "https", "ftp"})
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     * @Assert\Choice(choices={"sidebar", "content"}, message="Choose a valid location.")
     */
    private $location;

    /**
     * Set title
     *
     * @param string $title
     * @return Link
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

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
     * Set location
     *
     * @param string $location
     * @return Link
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return string 
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function addToForm(FormBuilder $builder) {
        $builder->add('title', 'text', array('label' => 'Title'))
            ->add('address', 'text', array('label' => 'URL:'))
            ->add('location', 'choice', array('choices' => array('sidebar' => 'Sidebar', 'content' => 'Main page'), 'label' => 'Location:'));
        return $builder;
    }
}