<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\src\Form\gear\__default;

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
        $builder->add('name' , TextType::class, [
            'constraints' => [
                new Assert\Length(['min'=>'1']),
                new Assert\Length(['max'=>'128']),
                new Assert\NotBlank(),
            ],
        ]);

        $builder->add('description' , TextType::class, [
            'constraints' => [
                new Assert\Length(['min'=>'1']),
                new Assert\Length(['max'=>'255']),
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