<?php

namespace Bio\InfoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
        $builder
            ->add('fName', 'text', array('label' => 'First name'))
            ->add('lName', 'text', array('label' => 'Last name'))
            ->add('email', 'email', array('label' => 'Email'))
            ->add('bldg', 'choice', array(
                'choices' => array_combine($array, $array),
                'empty_value' => '-',
                'required' => false,
                'label' => 'Building:'
                )
            )
            ->add('room', 'text', array('label' => 'Room:', 'required' => false))
            ->add('title', 'choice', array(
                'choices' => array(
                    'instructor' => 'Instructor',
                    'ta' => 'TA',
                    'coordinator' => 'Coordinator'
                ),
                'label' => 'Title:'
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
            'data_class' => 'Bio\InfoBundle\Entity\Person'
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
