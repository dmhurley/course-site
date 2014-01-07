<?php

namespace Bio\DataBundle\Twig;

class BioExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('safe', array($this, 'safeFilter'), array('is_safe' => array('all'))),
        );
    }

    public function safeFilter($string)
    {   
        // TODO html encode script tags and only script tags
        return string;
    }

    public function getName()
    {
        return 'bio_extension';
    }
}