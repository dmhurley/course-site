<?php

namespace Bio\DataBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BioExtension extends \Twig_Extension
{   

    // shamelessly stolen and modified from Symfony\Bridge\Twig\Extension\RoutingExtension.php
    private $router;
    public function __construct($router) {
        $this->router = $router;
        $path = $router->match($router->getContext()->getPathInfo());
        $this->path = array(
                'year' => isset($path['year']) ? $path['year'] : 2014,
                'quarter' => isset($path['quarter']) ? $path['quarter'] : 'win',
                'number' => isset($path['number']) ? $path['number'] : 180
            );
    }
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('internal', array($this, 'getInternalPath'), array('is_safe_callback' => array($this, 'isUrlGenerationSafe')))
        );
    }
    public function getInternalPath($name, $parameters = array(), $relative = false) {
        $parameters = array_merge($parameters, $this->path);
        return $this->router->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }
    public function isUrlGenerationSafe(\Twig_Node $argsNode)
    {
        // support named arguments
        $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : (
            $argsNode->hasNode(1) ? $argsNode->getNode(1) : null
        );

        if (null === $paramsNode || $paramsNode instanceof \Twig_Node_Expression_Array && count($paramsNode) <= 2 &&
            (!$paramsNode->hasNode(1) || $paramsNode->getNode(1) instanceof \Twig_Node_Expression_Constant)
        ) {
            return array('html');
        }

        return array();
    }


    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('safe', array($this, 'safeFilter'), array('is_safe' => array('all')))
        );
    }

    public function safeFilter($string)
    {   
        // TODO html encode script tags and only script tags
        return $string;
    }

    public function getName()
    {
        return 'bio_extension';
    }
}