<?php

namespace Bio\InfoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $array = file('bundles/bioinfo/buildings.txt', FILE_IGNORE_NEW_LINES);
        $builder
            ->add('courseNumber', 'text', array('label' => 'Course Number:'))
            ->add('title', 'text', array('label' => 'Course Name:'))
            ->add('qtr', 'choice', array(
                'choices' => array(
                        'autumn' => 'Autumn',
                        'winter' => 'Winter',
                        'spring' => 'Spring',
                        'summer' => 'Summer'
                    ), 
                'label' => 'Quarter'
                )
            )
            ->add('year', 'integer', array('label' => 'Year:'))
            ->add('email', 'email', array('label' => 'Email:'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\InfoBundle\Entity\Info'
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
