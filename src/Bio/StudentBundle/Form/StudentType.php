<?php

namespace Bio\StudentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

class StudentType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('sid', 'text', array(
				'label' => "Student ID:",
				'attr' => array(
					'pattern' => '[0-9]{7}',
					'title' => '7 digit student ID')
				)
			)
    		->add('fName', 'text', array('label' => "First Name:"))
    		->add('lName', 'text', array('label' => "Last Name:"))
            ->add('section', 'entity', array(
            	'label' => "Section:",
            	'class' => 'BioInfoBundle:Section',
            	'property' => 'name',
            	'empty_value' => '',
            	'query_builder' => function(EntityRepository $repo) {
            			return $repo->createQueryBuilder('s')
            				->orderBy('s.name', 'ASC');
            		}
            	)
            )
    		->add('email', 'email', array('label' => "Email:"))
    	;
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