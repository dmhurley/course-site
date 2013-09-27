<?php

namespace Bio\ExamBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExamType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('section')
            ->add('tDate')
            ->add('tStart')
            ->add('tEnd')
            ->add('tDuration')
            ->add('gDate')
            ->add('gStart')
            ->add('gEnd')
            ->add('gDuration')
            ->add('questions')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\ExamBundle\Entity\Exam'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bio_exambundle_exam';
    }
}
