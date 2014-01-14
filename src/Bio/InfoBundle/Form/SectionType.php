<?php

namespace Bio\InfoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
        $builder
            ->add('name', 'text', array(
                'label' => 'Name:',
                'attr' => array(
                    'pattern' => '^[A-Z][A-Z0-9]?$',
                    'title' => 'Valid capitalized section name.'
                    )
                )
            )
            ->add('days', 'choice', array(
                'label' => 'Day:',
                'choices' => array(
                    "m" => "Monday",
                    "tu" => "Tuesday",
                    "w" => "Wednesday",
                    "th" => "Thursday",
                    "f" => "Friday",
                    "sa" => "Saturday",
                    "su" => "Sunday"),
                'multiple' => true))
            ->add('start', 'time', array('label' => 'Start Time:'))
            ->add('end', 'time', array('label' => 'End Time:'))
            ->add('bldg', 'choice', array(
                'choices' => array_combine($array, $array),
                'validation_groups' => false,
                'label' => "Building:"
                )
            )
            ->add('room', 'text', array('label' => 'Room:'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\InfoBundle\Entity\Section'
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
