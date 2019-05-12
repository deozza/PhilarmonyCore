<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

trait AddFieldTrait{

    private function addFieldToForm($field, $config, $form)
    {
        $formOptions = [];
        $enumeration = null;
        if($config['array'] === true)
        {
            $class = CollectionType::class;
            $formOptions['entry_type'] = FieldTypes::ENUMERATION[$config['type']];
            $formOptions['entry_options']['constraints'] = [];
            foreach($config['constraints'] as $constraint=>$value)
            {
                $formOptions['entry_options']['constraints'] = array_merge($formOptions['entry_options']['constraints'], $this->addValueConstraint(explode('.', $constraint), $value));
            }
            $formOptions['allow_add'] = true;
            $formOptions['allow_delete'] = false;
        }
        else
        {
            $type = explode(".", $config['type']);
            if(isset($type[1])) $this->subType = $type[1];
            $class = FieldTypes::ENUMERATION[$type[0]];
            $formOptions['constraints'] = [];
            $formOptions = array_merge($formOptions,$this->addTypeConstraints($class));
            foreach($config['constraints'] as $constraint=>$value)
            {
                $formOptions['constraints'] = array_merge($formOptions['constraints'], $this->addValueConstraint(explode('.', $constraint), $value));
            }

            if(isset($config['constraints']['required']) && $config['constraints']['required'] === true)
            {
                array_push($formOptions['constraints'], new NotBlank());
            }
        }

        $form->add($field, $class, $formOptions);
    }

    private function addTypeConstraints($class)
    {
        $formOptions = [];

        switch($class)
        {
            case EntityType::class:
                {
                    $formOptions['class'] = Entity::class;
                    $formOptions['query_builder'] = function(EntityRepository $er)
                    {
                        return $er->createQueryBuilder('e')
                            ->where("e.kind = :kind")
                            ->setParameter(':kind', $this->subType);
                    };
                    $formOptions['choice_value'] = function(Entity $entity = null)
                    {
                        return $entity ? $entity->getUuidAsString() : '';
                    };

                };break;

            case ChoiceType::class:
                {
                    try
                    {
                        $enumeration = $this->schemaLoader->loadEnumerationEnumeration($this->subType);
                    }
                    catch(\Exception $e)
                    {
                        return $this->response->badRequest($e->getMessage());
                    }

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

        return $formOptions;
    }

    private function addValueConstraint($property, $value)
    {
        $constraints = [];
        if(in_array('length', $property) && !empty($value))
        {
            $length = [];
            if(in_array('min', $property) && !empty($value))
            {
                $length['min'] = $value;
            }
            if(in_array('max', $property) && !empty($value))
            {
                $length['max'] = $value;
            }
            $constraints[] = new Length($length);
        }

        if(in_array('greaterThanOrEqual', $property) && !empty($value))
        {
            $constraints[] = new GreaterThanOrEqual($value);
        }

        if(in_array('lesserThanOrEqual', $property) && !empty($value))
        {
            $constraints[] = new LessThanOrEqual($value);
        }

        if(in_array('greaterThan', $property) && !empty($value))
        {
            $constraints[] = new GreaterThan($value);
        }

        if(in_array('lesserThan', $property) && !empty($value))
        {
            $constraints[] = new LessThanOrEqual($value);
        }

        return $constraints;
    }
}