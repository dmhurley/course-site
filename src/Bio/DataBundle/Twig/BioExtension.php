<?php

namespace Bio\DataBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class BioExtension extends \Twig_Extension
{   

    // shamelessly stolen and modified from Symfony\Bridge\Twig\Extension\RoutingExtension.php
    private $request;
    private $router;
    private $logger;

    public function __construct($container) {
        $this->request = $container->get('request');
        $this->router = $container->get('router');
        $this->logger = $container->get('logger');
    }
    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('internal', array($this, 'getInternalPath'), array('is_safe_callback' => array($this, 'isUrlGenerationSafe')))
        );
    }
    public function getInternalPath($name, $parameters = array(), $relative = false) {
        $path = $this->router->match($this->request->getPathInfo());
        $defaultParameters = array(
            'year' => $path['year'],
            'quarter' => $path['quarter'],
            'number' => $path['number']
        );
        $parameters = array_merge($parameters, $defaultParameters);
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