<?php

namespace Bio\ExamBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QuestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('question', 'textarea', array(
                'label' => 'Question:',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('answer', 'textarea', array(
                'label' => 'Answer/Rubric:',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bio'
                    )
                )
            )
            ->add('points', 'integer', array('label' => 'Points:'))
            ->add('tags', 'text',      array('label' => 'Tags:',
                'mapped' => false,
                'required' => false,
                'attr' => array(
                    'pattern' => '[a-z\s]+',
                    'title' => 'Lower case tags seperated by spaces. a-z only.'
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
            'data_class' => 'Bio\ExamBundle\Entity\Question'
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
