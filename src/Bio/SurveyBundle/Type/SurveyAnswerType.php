<?php

namespace Bio\SurveyBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SurveyAnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('answer', null, array(
            'required' => false,
            'constraints' => new Assert\NotBlank()
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\SurveyBundle\Entity\SurveyAnswer'
        ));
    }

    public function getName()
    {
        return 'answer';
    }
}
