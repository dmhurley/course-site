<?php

namespace Bio\SurveyBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Bio\SurveyBundle\Type\SurveyQuestionType;

class SurveyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Survey Name'))
            ->add('anonymous', 'checkbox', array(
                'label' => 'Anonymous',
                'required' => false
            ))
            ->add('hidden', 'checkbox', array(
                'label' => 'Publish',
                'required' => false
            ))
            ->add('questions', 'collection', array(
                'label' => 'questions',
                'type' => new SurveyQuestionType(),
                'allow_add' => true
            ))
        ;
    }

    /**
    * @param OptionsResolverInterface $resolver
    */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\SurveyBundle\Entity\Survey'
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
