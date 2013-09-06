<?php

namespace Bio\InfoBundle\Entity;

use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"base" = "Base", "ann"="Announcement", "link"="Link", "person"="Person", "section"="Section", "hours"="Hours"})
 */
class Base {
	/**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id) {
    	$this->id = $id;
    }
}