<?php

namespace Bio\PublicBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PublicGlobal
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PublicGlobal
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
     * @var array
     *
     * @ORM\Column(name="showing", type="array")
     */
    private $showing;

    public function __construct() {
        $this->showing = [0,1,2,3,4,5,6,7,8,9];
    }

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
     * Set showing
     *
     * @param array $showing
     * @return PublicGlobal
     */
    public function setShowing($showing)
    {
        $this->showing = $showing;
    
        return $this;
    }

    /**
     * Get showing
     *
     * @return array 
     */
    public function getShowing()
    {
        return $this->showing;
    }
}
