<?php

namespace Bio\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;


class UserType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array(
                'label' => 'Username:',
                'constraints' => new Assert\NotBlank()
                )
            )
            ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'The password fields must match.',
                    'first_options' => array('label' => 'Password:'),
                    'second_options' => array('label' => 'Repeat:')
                )
            )
            ->add('email', 'text', array(
                'label' => 'Email:',
                'constraints' => new Assert\Email()
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
            'data_class' => 'Bio\UserBundle\Entity\User'
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
