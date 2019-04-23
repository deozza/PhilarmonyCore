<?php


namespace Deozza\PhilarmonyBundle\Service\FormManager;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FieldTypes
{
    const ENUMERATION = [
        "string" => TextType::class,
        "date" => DateType::class,
        "int" => IntegerType::class,
        "price" => MoneyType::class,
        "enumeration" => ChoiceType::class,
        "entity" => EntityType::class,
        "file" => FileType::class,
        "embedded" => "embedded",
        "array" => CollectionType::class
    ];
}