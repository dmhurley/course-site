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
            ->add('title', 'text', array('label'=>'Exam Name:'))
            ->add('section', 'text', array(
                'label'=>'Section:',
                'required' => false,
                'empty_data' => '',
                'attr' => array(
                    'pattern' => '(\A\Z)|(^[A-Z][A-Z0-9]?$)',
                    'title' => 'One or two letter capitalized section name.'
                    )
                )
            )
            ->add('tDate', 'date',        array('label' => 'Test Date:'))
            ->add('tStart', 'time',       array('label'=>'Test Start:'))
            ->add('tEnd', 'time',         array('label'=>'Test End:'))
            ->add('tDuration', 'integer', array('label'=>'Test Length (m):'))
            ->add('gDate', 'date',        array('label' => 'Grading Date:'))
            ->add('gStart', 'time',       array('label'=>'Grading Start:'))
            ->add('gEnd', 'time',         array('label'=>'Grading End:'))
            ->add('gDuration', 'integer', array('label'=>'Grade Length (m):'))
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
