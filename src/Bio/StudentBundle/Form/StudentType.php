<?php

namespace Bio\StudentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class StudentType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('sid', 'text', array('label' => "Student ID:", 'read_only' => $options['edit'], 'attr' => array('pattern' => '[0-9]{7}', 'title' => '7 digit student ID')))
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
            ->add('section', 'text', array('label' => "Section:"))
    		->add('email', 'email', array('label' => "Email:"))
    		->add($options['title'], 'submit');
	}

	// make sure to add all the default options you might add
	// AFFECTS THE ENTIRE FORM
	public function buildView(FormView $view, FormInterface $form, array $options) {
		$view->vars = array_replace($view->vars, $options);
	}

	public function getName() {
		return 'form';
	}

	// special options
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'title'		=> 'submit',
            'edit'		=> false,
        ));
    }
}