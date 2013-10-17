<?php

namespace Bio\ExamBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class GradeType extends AbstractType
{      
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', 'textarea', array(
                'constraints' => array(
                        new Assert\NotNull(),
                        new Assert\NotBlank()
                    )
                )
            )
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function(FormEvent $event){
                    $points = $event->getData()->getAnswer()->getQuestion()->getPoints();
                    $event->getForm()->add('points', 'choice', array(
                        'choices' => range(0,$points),
                        'constraints' => array(
                                new Assert\NotNull(),
                                new Assert\NotBlank()
                            ),
                        'empty_value' => '-'
                        )
                    );
                }
            )
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bio\ExamBundle\Entity\Grade'
        ));
    }

    public function getName()
    {
        return 'grade';
    }
}