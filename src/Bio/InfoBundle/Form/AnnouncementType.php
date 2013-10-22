<?php

namespace Bio\InfoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnouncementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->add('timestamp', 'datetime', array(
                'attr' => array('class' => 'datetime'),
                'label' => 'Start Time:'
                )
            )
            ->add('expiration', 'datetime', array(
                'attr' => array('class' => 'datetime'),
                'label' => 'End Time:'
                )
            )
            ->add('text', 'textarea', array('label' => 'Announcement'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\InfoBundle\Entity\Announcement'
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
