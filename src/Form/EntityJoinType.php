<?php

namespace Deozza\PhilarmonyBundle\Form;

use Deozza\PhilarmonyBundle\Entity\EntityJoin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityJoinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entity1uuid')
            ->add('entity2uuid')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           "data_class"=>EntityJoin::class,
            "csrf_protection"=>false
        ]);
    }
}
