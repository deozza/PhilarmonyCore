<?php

namespace Deozza\PhilarmonyCoreBundle\Service\Validation;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;

class Validate
{
    const METHODS = [
        "greaterThan" => ">",
        "lesserThan" => "<",
        "greaterThanOrEqual" => ">=",
        "lesserThanOrEqual" => "<=",
        "between" => "between",
        "notBetween" => "!between",
        "equal" => "==",
        "notEqual" => "!="
    ];

    public function __construct(ResponseMaker $responseMaker, DatabaseSchemaLoader $schemaLoader, EntityManagerInterface $em)
    {
        $this->response = $responseMaker;
        $this->schemaLoader = $schemaLoader;
        $this->em = $em;
    }

    public function processValidation(Entity $entity,int $stateToValidate, array $entityStates, $user,int $lastState = null)
    {
        $states = array_keys($entityStates);

        if(!array_key_exists($stateToValidate, $states))
        {
            $entity->setValidationState($states[$lastState]);
            return true;
        }
        $stateToValidateConfig = $entityStates[$states[$stateToValidate]];

        if(!array_key_exists('constraints', $stateToValidateConfig))
        {
            if(array_key_exists($stateToValidate + 1,$states))
            {
                return $this->processValidation($entity, $stateToValidate + 1, $entityStates, $user, $stateToValidate);
            }

            $entity->setValidationState($states[$stateToValidate]);
            return true;
        }

        $validate = [];

        if(array_key_exists('manual', $stateToValidateConfig['constraints']))
        {
            $validate['manual'] = ["manual"=>false];
        }

        if(array_key_exists('properties', $stateToValidateConfig['constraints']))
        {
            foreach($stateToValidateConfig['constraints']['properties'] as $x=>$y)
            {
                $validate[$x] = $this->validateField($x, $y, $entity);
            }
        }

        foreach($validate as $key=>$value)
        {
            if(in_array(false, $value))
            {
                $entity->setValidationState($states[$lastState]);

                return $validate;
            }
        }

        if(array_key_exists($stateToValidate + 1,$states))
        {
            return $this->processValidation($entity, $stateToValidate + 1, $entityStates, $user, $stateToValidate);
        }
        $entity->setValidationState($states[$stateToValidate]);

        return true;
    }

    private function validateField(string $x, array $y, Entity $entity)
    {
        $validate = [];

        if($x === "manual")
        {
            $validate[$x] = false;
            return $validate;
        }
        $a = $this->getPropertyValue($x, $entity);
        foreach($y as $constraint)
        {
            $method = $this->getMethod($constraint);
            $referenceEntity = $this->getReferenceEntity($constraint, $entity);
            $referenceProperties = $this->getReferenceProperties($constraint);
            $validate[$constraint] = $this->validate($a, $method, $referenceProperties, $referenceEntity);
        }
        return $validate;
    }

    private function getPropertyValue(string $x, Entity $entity)
    {
        $field = explode('.', $x);
        $property = $entity->getProperties();
        for($i = 0; $i < count($field); $i++)
        {
            if(is_array($property))
            {
                $property = $property[$field[$i]];
            }
            else
            {
                $function = 'get'.ucfirst($field[$i]);
                $property = $property->{$function}();
            }
        }

        return $property;
    }

    private function getMethod(string $constraint)
    {
        $method = explode('.', $constraint);
        if(!isset(self::METHODS[$method[0]]))
        {
            $method = explode('(', $constraint);
        }

        return self::METHODS[$method[0]];
    }

    private function getReferenceEntity(string $constraint, Entity $entity)
    {
        $referenceEntity = explode('.',$constraint);
        $referenceEntity = explode('(',$referenceEntity[1]);
        $referenceEntity = substr($referenceEntity[0], 0, strlen($referenceEntity[1]) - 1);
        if($referenceEntity === "self")
        {
            return $entity;
        }

        return $referenceEntity;
    }

    private function getReferenceProperties(string $constraint)
    {
        $properties = explode('(', $constraint);
        $properties = substr($properties[1],0, strlen($properties[1]) -1);
        return explode(',', $properties);
    }

    private function validate($sentValue, $method, $expectedValue, $referenceEntity)
    {
        $sentValue = $this->getTimestampFromDatetime($sentValue);
        if(is_object($referenceEntity))
        {
            $properties = $referenceEntity->getProperties();

            $propertyA = $this->extractValueFromSelf($expectedValue[0], $properties);
            if(count($expectedValue) > 1)
            {
                $propertyB = $this->extractValueFromSelf($expectedValue[1], $properties);
                return $this->compare($sentValue, $method, $propertyA, $propertyB);
            }
            return $this->compare($sentValue, $method, $propertyA);
        }
        elseif($referenceEntity === "value")
        {
            $propertyA= $this->extractValue($expectedValue[0]);

            if(count($expectedValue) > 1)
            {
                $propertyB = $this->extractValue($expectedValue[1]);
                return $this->compare($sentValue, $method, $propertyA, $propertyB);
            }
            return $this->compare($sentValue, $method, $propertyA);
        }
        else
        {
            if(strpos($method, "between"))
            {
                $result = $this->em->getRepository(Entity::class)->findAllBetweenForValidate($referenceEntity,  $expectedValue[0], $expectedValue[1], $sentValue);
            }
            else
            {
                $result = $this->em->getRepository(Entity::class)->findAllForValidate($referenceEntity, $expectedValue[0], $sentValue, $method);
            }

            if(substr($method, 0, 1) === "!" && count($result) > 0)
            {
                return false;
            }
            elseif (substr($method, 0, 1) !== "!" && count($result) === 0)
            {
                return false;
            }

            return true;
        }
    }

    private function extractValueFromSelf($expectedValue, $properties)
    {
        $explodedField = explode('.', $expectedValue);

        for($i = 0; $i < count($explodedField); $i++)
        {
            if(is_object($properties))
            {
                $get = "get".ucfirst($explodedField[$i]);
                $properties = $properties->{$get}();
            }
            else
            {
                $properties = $properties[$explodedField[$i]];
            }
        }

        return $this->getTimestampFromDatetime($properties);
    }

    private function extractValue($expectedValue)
    {
        $explodedExpectedValue = explode('.', $expectedValue);
        if($explodedExpectedValue[0] === 'date')
        {
            $expectedValue = new \DateTime($explodedExpectedValue[1]);
            $expectedValue = $expectedValue->getTimestamp();
        }
        else
        {
            $expectedValue = $explodedExpectedValue[0];
        }

        return $expectedValue;
    }

    private function getTimestampFromDatetime($property)
    {
        if(is_a($property, \DateTime::class))
        {
            $property = $property->getTimestamp();
        }

        return $property;
    }

    private function compare($sentValue, $method, $propertyA, $propertyB = null)
    {
        if(!empty($propertyB))
        {
            if(substr($method, 0, 1) === '!')
            {
                eval("\$result = '$sentValue < '$propertyA' && '$sentValue' > '$propertyB';");
            }
            else
            {
                eval("\$result = '$sentValue >= '$propertyA' && '$sentValue' <= '$propertyB';");
            }

            return $result;
        }
        eval("\$result = '$sentValue' $method '$propertyA';");

        return $result;
    }
}