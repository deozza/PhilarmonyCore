<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Form\reservation\__default;

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
use Deozza\PhilarmonyCoreBundle\Document\Entity;

class POST extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date_begin' , DateTimeType::class, [
            'constraints' => [
                new Assert\DateTime(),
                new Assert\NotBlank(),
            ],
            'widget' => 'single_text'
        ]);

        $builder->add('date_end' , DateTimeType::class, [
            'constraints' => [
                new Assert\DateTime(),
                new Assert\NotBlank(),
            ],
            'widget' => 'single_text'
        ]);

        $builder->add('nbPerson' , IntegerType::class, [
            'constraints' => [
                new Assert\GreaterThanOrEqual(1),
                new Assert\NotBlank(),
                ],
        ]);

        $builder->add('annonce', EntityType::class, [
            'class' => Entity::class,
            'query_builder'=> function(EntityRepository $er){
                return $er->createQueryBuilder('e')
                ->where("e.kind = :kind")
                ->setParameter(':kind', 'annonce');
            },
            'choice_value' =>  function(Entity $entity = null){
                return $entity ? $entity->getUuidAsString() : '';
            }
        ]);

        $builder->add('paid', HiddenType::class, [
            'data' => 'false',
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