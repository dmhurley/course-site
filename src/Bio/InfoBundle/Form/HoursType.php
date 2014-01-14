<?php

namespace Bio\InfoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HoursType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
            ->add('person', 'entity', array(
                'class' => 'BioInfoBundle:Person',
                'property' => 'fullName',
                'label' => 'Instructor:'
                )
            )
            ->add('days', 'text', array(
                'label' => 'Days',
                'required' => false
                )
            )
            ->add('start', 'time', array('label' => 'Start Time:'))
            ->add('end', 'time', array('label' => 'End Time:'))
            ->add('byAppointment', 'checkbox', array(
                'required' => false,
                'label' => 'By Appointment?'
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
            'data_class' => 'Bio\InfoBundle\Entity\Hours'
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
