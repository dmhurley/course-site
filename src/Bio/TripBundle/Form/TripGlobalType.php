<?php

namespace Bio\TripBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TripGlobalType extends AbstractType
{

    private $guidePass = '';

    public function __construct($global) {
        $this->guidePass = $global->getGuidePass();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add('opening', 'datetime', array(
                'label' => 'Signup Start:',
                'widget' => 'single_text',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('closing', 'datetime', array(
                'label' => 'Evaluations Due:',
                'widget' => 'single_text',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('maxTrips', 'integer', array('label' => "Max Trips:"))
            ->add('evalDue', 'integer',  array('label' => "Days Until Late:"))
            ->add('notifications', 'checkbox', array(
                'label' => 'Notifications:',
                'required' => false
                )
            )
            ->add('start', 'datetime', array(
                'label' => 'Start:',
                'widget' => 'single_text',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('instructions', 'textarea', array(
                'label' => 'Trip Instructions',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('guidePass', 'password', array(
                'label' => 'Leader Password:',
                'always_empty' => false,
                'attr' => array('value' => $this->guidePass)
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\TripBundle\Entity\TripGlobal'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form';
    }
}
