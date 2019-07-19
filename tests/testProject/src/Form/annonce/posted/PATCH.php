<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Form\annonce\posted;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Deozza\PhilarmonyCoreBundle\Entity\Entity;

class PATCH extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title' , TextType::class, [
            'constraints' => [
                new Assert\Length(['min'=>'1']),
                new Assert\Length(['max'=>'100']),
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('description' , CollectionType::class, [
            'entry_type' => TextType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_options' => [
                'constraints' => [
                    new Assert\Length(['min'=>'1']),
                ],
            ]
        ]);

        $builder->add('price' , NumberType::class, [
            'constraints' => [
                new Assert\GreaterThanOrEqual(1),
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('annonce_category' , CollectionType::class, [
            'entry_type' => ChoiceType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_options' => [
                'choices' =>[ 'maison', 'appartement',],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]
        ]);


        $builder->add('nbPersonMax' , IntegerType::class, [
            'constraints' => [
                new Assert\GreaterThanOrEqual(1),
                new Assert\NotBlank(),
                ],
        ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => null,
                'csrf_protection' => false
            ]
        );
    }
}