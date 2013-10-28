<?php

namespace Bio\FolderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FolderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Name:'))
            ->add('private', 'checkbox', array(
                'label' => 'Private:',
                'required' => false,
                )
            )
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
            'data_class' => 'Bio\FolderBundle\Entity\Folder'
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
