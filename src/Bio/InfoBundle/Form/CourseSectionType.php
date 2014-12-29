<?php

namespace Bio\InfoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CourseSectionType extends AbstractType
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
                    'pattern' => '^[A-Z]$',
                    'title' => 'Valid capitalized course section name.'
                    )
                )
            )
            ->add('days', 'choice', array(
                'choices' => array(
                     'm' => 'Monday',
                     'tu' => 'Tuesday',
                     'w' => 'Wednsday',
                     'th' => 'Thursday',
                     'f' => 'Friday',
                     'sa' => 'Saturday'
                 ),
                'multiple' => true,
                'label' => 'Days:'
                )
            )
            ->add('startTime', 'time', array('label' => 'Start:'))
            ->add('endTime', 'time', array('label' => 'End:'))
            ->add('bldg', 'choice', array(
                'choices' => array_combine($array, $array),
                'label' => 'Building:'
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
            'data_class' => 'Bio\InfoBundle\Entity\CourseSection'
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
