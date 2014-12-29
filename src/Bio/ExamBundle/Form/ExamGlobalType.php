<?php

namespace Bio\ExamBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExamGlobalType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('grade', 'integer', array('label' => "Tests To Grade:"))
            ->add('comments', 'checkbox', array(
                'label' => 'Force Comments',
                'required' => false
                )
            )
            ->add('reviewHours', 'integer', array('label' => 'Hours to review:'))
            ->add('rules', 'textarea', array('label' => "Test Rules:"))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\ExamBundle\Entity\ExamGlobal'
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
