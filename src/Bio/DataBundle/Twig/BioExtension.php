<?php

namespace Bio\DataBundle\Twig;

class BioExtension extends \Twig_Extension {
	public function getFilters() {
		return array(
			new \Twig_SimpleFilter('jsonify', array($this, 'jsonifyFilter'))
		);
	}

	public function jsonifyFilter($object) {
		$class = get_class($object);
		$array = (array)$object;
		$builder = [];
		foreach($array as $key => $value) {
			$key = substr($key, strlen($class) + 2);

			try {
				$builder[] ='"'.$key.'":'.$this->transformValue($value);
			} catch (\Exception $e) {}
		} 
		return "{\n".implode(",\n", $builder)."}\n";
	}

	private function transformValue($value) {
		if ($value instanceof \DateTime) {
			return '"'.$value->format('Y-m-d H:i:s').'"';
		}

		if (is_numeric($value)) {
			return $value;
		}

		if (is_string($value)) {
			return '"'.$value.'"';
		}

		throw new \Exception();
	}

	public function getName() {
		return 'bio_extension';
	}
}