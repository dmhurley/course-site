<?php

namespace Bio\FolderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;


class FileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('file', 'file', array(
                'label' => false, 
                'constraints' => new Assert\File(
                        array(
                            "maxSize" => "32M"
                        )
                    )
                )
            )
            ->add('name', 'text', array('label' => 'Name:'))
            ->add('parent', 'entity', array(
                    'class' => 'BioFolderBundle:Folder',
                    'property' => 'name'
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
            'data_class' => 'Bio\FolderBundle\Entity\File'
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
