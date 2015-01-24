<?php

namespace Bio\SurveyBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SurveyAnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            $data = $event->getData()->getQuestion()->getData();
            $form = $event->getForm();

            if (count($data) > 1) {
                $form->add('answer', 'choice', array(
                    'choices' => array_slice($data, 1),
                    'expanded' => true
                ));
            } else {
                $form->add('answer', null);
            }
        });
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
