<?php

namespace Bio\FolderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LinkType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Title:'))
            ->add('address', 'text', array('label' => 'URL:'))
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
            'data_class' => 'Bio\FolderBundle\Entity\Link'
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
