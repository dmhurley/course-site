<?php

namespace Bio\ClickerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;


class ClickerType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('cid', 'text', array(
                'label' => "Clicker ID:",
                'constraints' => array(
                    new Assert\Regex(array(
                        "pattern" => "/^[0-9A-Fa-f]{6}$/",
                        "message" => "6 digit clicker ID (0-9 A-F).")),
                    new Assert\NotBlank()
                    ),
                'attr' => array(
                    'pattern' => '[0-9A-Fa-f]{6}',
                    'title' => '6 digit clicker ID (0-9 A-F).'
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
            'data_class' => 'Bio\ClickerBundle\Entity\Clicker'
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
