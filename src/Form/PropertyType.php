<?php

namespace Deozza\PhilarmonyBundle\Form;

use Deozza\PhilarmonyBundle\Entity\Property;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           "data_class"=>Property::class,
            "csrf_protection"=>false
        ]);
    }
}
