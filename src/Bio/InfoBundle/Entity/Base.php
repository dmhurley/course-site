<?php

namespace Bio\InfoBundle\Entity;

use Symfony\Component\Form\FormBuilder;

abstract class Base {
	abstract public function addToForm(FormBuilder $builder);
	abstract public function setAll($entity);
	abstract public function setId($id);
}