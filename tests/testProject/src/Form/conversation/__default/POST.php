<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Form\conversation\__default;

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

class POST extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('messageTitle' , TextType::class, [
            'constraints' => [
                new Assert\Length(['min'=>'1']),
                new Assert\Length(['max'=>'100']),
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('messageContent' , TextType::class, [
            'constraints' => [
                new Assert\Length(['min'=>'1']),
                new Assert\Length(['max'=>'255']),
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('receiver' , TextType::class, [
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('dateOfPost', HiddenType::class, [
            'data' => new \DateTime('now'),
            'disabled'=>true
        ]);
        $builder->add('seen', HiddenType::class, [
            'data' => '[]',
            'disabled'=>true
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