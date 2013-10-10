<?php

namespace Bio\DataBundle\Twig;

class BioExtension extends \Twig_Extension {

	private $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function getFilters() {
		return array(
			new \Twig_SimpleFilter('jsonify', array($this, 'jsonifyFilter'))
		);
	}

	public function jsonifyFilter($object) {
		$serializer = $this->container->get('jms_serializer');
		return $serializer->serialize($object, 'json');
	}

	public function getName() {
		return 'bio_extension';
	}
}