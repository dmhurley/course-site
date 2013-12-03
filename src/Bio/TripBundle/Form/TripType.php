<?php

namespace Bio\TripBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TripType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('label' => 'Title:'))
            ->add('start', 'datetime', array(
                'widget' => 'single_text',
                'label' => 'Start:',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('end', 'datetime', array(
                'label' => 'End:',
                'widget' => 'single_text',
                'attr' => array('class' => 'datetime')
                )
            )
            ->add('max', 'integer', array('label' => 'Limit:'))
            ->add('email', 'email', array('label' => 'Leader Email:'))
            ->add('shortSum', 'textarea', array(
                'label' => 'Short Summary:',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('longSum', 'textarea', array(
                'label' => 'Long Summary:',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
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
            'data_class' => 'Bio\TripBundle\Entity\Trip'
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
