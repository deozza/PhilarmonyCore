<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaEmptyOrHeadMissingException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaInvalidValueTypeException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingValueException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedValueException;

trait ValidatePropertyTrait
{
    private function validateProperty(string $property, array $propertyContent)
    {
        $authorizedKeys = ['type', 'constraints', 'array'];
        $keys = array_keys($propertyContent);

        if(!in_array($authorizedKeys[0], $keys) || !in_array($authorizedKeys[1], $keys))
        {
            throw new DataSchemaMissingKeyException();
        }

        foreach($propertyContent as $key => $keyContent)
        {
            if(empty($keyContent))
            {
                throw new DataSchemaEmptyOrHeadMissingException();
            }

            switch($key)
            {
                case $authorizedKeys[0] : $this->validateType($property, $keyContent);
                break;
                case $authorizedKeys[1] : $this->validateConstraints($property, $keyContent);
                break;
                case $authorizedKeys[2] : $this->validateArray($property, $keyContent);
                break;
                default : throw new DataSchemaUnexpectedKeyException();
                break;
            }
        }
    }

    private function validateType(string $property, $type)
    {
        if(!is_string($type))
        {
            throw new DataSchemaInvalidValueTypeException();
        }

        $explodedType = explode('.', $type);

        if(!array_key_exists($explodedType[0],\Deozza\PhilarmonyUtils\Forms\FieldTypes::ENUMERATION))
        {
            throw new DataSchemaUnexpectedKeyException($explodedType[0]);
        }

        if(count($explodedType) === 2)
        {
            switch($explodedType[0])
            {
                case 'embedded' : $this->checkEmbeddedExists($property, $explodedType[1]);
                break;
                case 'entity' : $this->checkEntityExists($property, $explodedType[1]);
                break;
                case 'enumeration' : $this->checkEnumerationExists($property, $explodedType[1]);
                break;
                default : throw new DataSchemaInvalidValueTypeException("Only 'embedded', 'entity' and 'enumeration' types have a sub-type. '$type' of property '$property' is invalid.");
                break;
            }
        }
    }

    private function checkEmbeddedExists(string $property, string $embedded)
    {
        if(!array_key_exists($embedded,$this->entities['entities']) || count(array_keys($this->entities['entities'][$embedded]))> 1)
        {
            throw new DataSchemaMissingValueException("'$embedded' (from '$property') was expected to be defined in entity config file. It was not found or it was badly formated.");
        }
    }

    private function checkEntityExists(string $property, string $entity)
    {
        if(!array_key_exists($entity,$this->entities['entities']))
        {
            throw new DataSchemaMissingValueException("'$entity' (from '$property') was expected to be defined in entity config file. It was not found.");
        }

        if(!array_key_exists('properties',$this->entities['entities'][$entity]) || !array_key_exists('states',$this->entities['entities'][$entity]))
        {
            throw new DataSchemaMissingValueException("'$entity' (from '$property') is badly formated.");
        }
    }

    private function checkEnumerationExists(string $property, string $enumeration)
    {
        if(!array_key_exists($enumeration,$this->enumerations['enumerations']))
        {
            throw new DataSchemaMissingValueException("'$enumeration' (from '$property') was expected to be defined in enumeration config file. It was not found.");
        }
    }

    private function validateConstraints(string $property, $constraints)
    {

    }

    private function validateArray(string $property, $array)
    {
        if($array !== true)
        {
            throw new DataSchemaUnexpectedValueException("'array' key of a property must be equal to 'true' if defined. ".json_encode($array)." found in '$property'.");
        }
    }
}