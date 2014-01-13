<?php

namespace Bio\FolderBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityRepository;

class StudentFolderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', 'entity', array(
                'label' => 'Parent:',
                'class' => 'BioFolderBundle:Folder',
                'property' => 'name',
                'query_builder' => function(EntityRepository $repo) {
                        return $repo->createQueryBuilder('f')
                            ->where('f.parent IS NULL');
                    }
                )
            )
            ->add('confirmation', 'checkbox', array(
                'label' => "Are you sure?",
                'required' => false,
                'constraints' => new Assert\True()
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
            'data_class' => null
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
