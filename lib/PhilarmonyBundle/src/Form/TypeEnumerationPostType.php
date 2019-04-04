<?php

namespace Deozza\PhilarmonyBundle\Form;

use Deozza\PhilarmonyBundle\Entity\TypePost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeEnumerationPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('regex')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           "data_class"=>TypePost::class,
            "csrf_protection"=>false
        ]);
    }
}
