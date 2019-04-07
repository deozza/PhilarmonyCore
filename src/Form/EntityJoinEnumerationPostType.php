<?php

namespace Deozza\PhilarmonyBundle\Form;

use Deozza\PhilarmonyBundle\Entity\EntityPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityJoinEnumerationPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('entity1', ChoiceType::class,[
                'choices'=>$options['entities'],
                'multiple'=>false
            ])
            ->add('entity2', ChoiceType::class,[
                'choices'=>$options['entities'],
                'multiple'=>false
            ])
            ->add('properties', ChoiceType::class,[
                'choices'=>$options['properties'],
                'multiple'=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           "data_class"=>EntityPost::class,
            "csrf_protection"=>false
        ]);

        $resolver->setRequired('entities');
        $resolver->setRequired('properties');
    }
}
