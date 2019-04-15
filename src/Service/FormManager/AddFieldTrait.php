<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

trait AddFieldTrait{
    private function addFieldToForm($field, $form, $isAnEntity)
    {
        $property = $this->schemaLoader->loadPropertyEnumeration($field);

        $this->type = explode(".", $property['type']);
        $class = FieldTypes::ENUMERATION[$this->type[0]];

        if(!$isAnEntity)
        {
            $field = "value";
        }

        $formOptions = $this->addConstraintsToField($class, $property);

        if(!empty($formOptions))
        {
            $form->add($field, $class, $formOptions);
        }
    }

    private function addConstraintsToField($class, $property)
    {
        $constraints = [];
        $formOptions = [];
        if($property['required'])
        {
            $constraints[] = new NotBlank();
        }

        switch($class)
        {
            case EntityType::class:
                {
                    $formOptions['class'] = Entity::class;
                    $formOptions['query_builder'] = function(EntityRepository $er)
                    {
                        return $er->createQueryBuilder('e')
                            ->where("e.kind = :kind")
                            ->setParameter(':kind', $this->type[1]);
                    };
                    $formOptions['choice_value'] = function(Entity $entity = null)
                    {
                        return $entity ? $entity->getUuidAsString() : '';
                    };

                };break;

            case ChoiceType::class:
                {
                    $enumeration = $this->schemaLoader->loadEnumerationEnumeration($this->type[1]);
                    $formOptions['choices'] = $enumeration;
                };break;

            case DateType::class:
                {
                    $formOptions['widget'] = "single_text";
                    $formOptions['format'] = "yyyy-MM-dd";
                };break;

            case FileType::class: return;break;
            default: break;
        }

        $constraints = $this->constraintGenerator($property, $constraints);

        $formOptions['constraints'] = $constraints;
        return $formOptions;
    }

    private function constraintGenerator($property, $constraints)
    {
        if(array_key_exists('length', $property) && !empty($property['length']))
        {
            $length = [];
            if(array_key_exists('min', $property['length']) && !empty($property['length']['min']))
            {
                $length['min'] = $property['length']['min'];
            }
            if(array_key_exists('max', $property['length']) && !empty($property['length']['max']))
            {
                $length['max'] = $property['length']['max'];
            }
            $constraints[] = new Length($length);
        }

        if(array_key_exists('greaterThanOrEqual', $property) && !empty($property['greaterThanOrEqual']))
        {
            $gtoe = $property['greaterThanOrEqual'];
            if(is_array($gtoe) && array_key_exists('propertyPath', $gtoe) && !empty($gtoe['propertyPath']))
            {
                $gtoe = $gtoe['propertyPath'];
            }
            $constraints[] = new GreaterThanOrEqual($gtoe);
        }

        if(array_key_exists('lessThanOrEqual', $property) && !empty($property['lessThanOrEqual']))
        {
            $ltoe = $property['lessThanOrEqual'];
            if(is_array($ltoe) && array_key_exists('propertyPath', $ltoe) && !empty($ltoe['propertyPath']))
            {
                $ltoe = $ltoe['propertyPath'];
            }
            $constraints[] = new LessThanOrEqual($ltoe);
        }

        if(array_key_exists('greaterThan', $property) && !empty($property['greaterThan']))
        {
            $gt = $property['greaterThan'];
            if(is_array($gt) && array_key_exists('propertyPath', $gt) && !empty($gt['propertyPath']))
            {
                $gt = $gt['propertyPath'];
            }
            $constraints[] = new GreaterThan($gt);
        }

        if(array_key_exists('lessThan', $property) && !empty($property['lessThan']))
        {
            $lt = $property['lessThan'];
            if(is_array($lt) && array_key_exists('propertyPath', $lt) && !empty($lt['propertyPath']))
            {
                $lt = $lt['propertyPath'];
            }
            $constraints[] = new LessThanOrEqual($lt);
        }

        return $constraints;
    }
}