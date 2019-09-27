<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Form\character\posted\owned_gear;

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
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Deozza\PhilarmonyCoreBundle\Document\Entity;

class POST extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('gear', DocumentType::class, [
            'class' => Entity::class,
            'query_builder'=> function(DocumentRepository $dr){
                return $dr->createQueryBuilder()->find(Entity::class)
                    ->eagerCursor(true)
                    ->field('kind')->equals('gear');
            },
            'choice_value' => function(Entity $entity = null){
                return $entity ? $entity->getUuidAsString() : '';
            },
            'constraints'=>[
                new Assert\NotBlank()
            ]
        ]);

        $builder->add('stock' , IntegerType::class, [
            'constraints' => [
                new Assert\GreaterThanOrEqual(0),
                new Assert\NotBlank(),
                ],
        ]);

        $builder->add('equiped' , TextType::class, [
            'constraints' => [
                new Assert\Choice([
                    'choices' =>[ '1', '',],
                    'strict' => true
                ]),
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