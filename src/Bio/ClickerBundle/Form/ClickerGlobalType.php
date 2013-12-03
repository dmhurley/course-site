<?php

namespace Bio\ClickerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ClickerGlobalType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notifications', 'checkbox', array(
                'label' => 'Notifications:',
                'required' => false
                )
            )
            ->add('start', 'datetime', array(
                'label' => 'Start:',
                'attr' => array('class' => 'datetime')
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
            'data_class' => 'Bio\ClickerBundle\Entity\ClickerGlobal'
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
