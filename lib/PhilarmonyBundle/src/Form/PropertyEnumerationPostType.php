<?php

namespace Deozza\PhilarmonyBundle\Form;

use Deozza\PhilarmonyBundle\Entity\PropertyPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropertyEnumerationPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('type', ChoiceType::class,[
                'choices'=>$options['types'],
                'multiple'=>false
            ])
            ->add('is_required')
            ->add('unique')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           "data_class"=>PropertyPost::class,
            "csrf_protection"=>false
        ]);

        $resolver->setRequired('types');
    }
}
