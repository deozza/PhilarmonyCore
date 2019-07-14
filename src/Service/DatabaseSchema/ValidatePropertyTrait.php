<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaEmptyOrHeadMissingException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaInvalidValueTypeException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingValueException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedValueException;
use Deozza\PhilarmonyUtils\DataSchema\AuthorizedKeys;

trait ValidatePropertyTrait
{
    private function validateProperty(string $property, array $propertyContent)
    {
        $authorizedKeys = AuthorizedKeys::PROPERTY_KEYS;
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
                case AuthorizedKeys::TYPES[7] : $this->checkEmbeddedExists($property, $explodedType[1]);
                    break;
                case AuthorizedKeys::TYPES[6] : $this->checkEntityExists($property, $explodedType[1]);
                    break;
                case AuthorizedKeys::TYPES[5] : $this->checkEnumerationExists($property, $explodedType[1]);
                    break;
                default : throw new DataSchemaInvalidValueTypeException("Only '".AuthorizedKeys::TYPES[7]."', '".AuthorizedKeys::TYPES[6]."' and '".AuthorizedKeys::TYPES[5]."' types have a sub-type. '$type' of property '$property' is invalid.");
                    break;
            }
        }
    }

    private function checkEmbeddedExists(string $property, string $embedded)
    {
        if(!array_key_exists($embedded,$this->entities[AuthorizedKeys::ENTITY_HEAD]) || count(array_keys($this->entities[AuthorizedKeys::ENTITY_HEAD][$embedded]))> 1)
        {
            throw new DataSchemaMissingValueException("'$embedded' (from '$property') was expected to be defined in entity config file. It was not found or it was badly formated.");
        }
    }

    private function checkEntityExists(string $property, string $entity)
    {
        if(!array_key_exists($entity,$this->entities[AuthorizedKeys::ENTITY_HEAD]))
        {
            throw new DataSchemaMissingValueException("'$entity' (from '$property') was expected to be defined in entity config file. It was not found.");
        }

        if(!array_key_exists(AuthorizedKeys::ENTITY_KEYS[0],$this->entities[AuthorizedKeys::ENTITY_HEAD][$entity]) || !array_key_exists(AuthorizedKeys::ENTITY_KEYS[1],$this->entities[AuthorizedKeys::ENTITY_HEAD][$entity]))
        {
            throw new DataSchemaMissingValueException("'$entity' (from '$property') is badly formated.");
        }
    }

    private function checkEnumerationExists(string $property, string $enumeration)
    {
        if(!array_key_exists($enumeration,$this->enumerations[AuthorizedKeys::ENUMERATION_HEAD]))
        {
            throw new DataSchemaMissingValueException("'$enumeration' (from '$property') was expected to be defined in enumeration config file. It was not found.");
        }
    }

    private function validateConstraints(string $property, array $constraints)
    {
        $authorizedKeys = AuthorizedKeys::PROPERTY_CONSTRAINT_KEYS;
        if(!array_key_exists($authorizedKeys[0], $constraints) || !array_key_exists($authorizedKeys[1], $constraints))
        {
            throw new DataSchemaMissingKeyException("'".$authorizedKeys[0]."' and '".$authorizedKeys[1]."' must be defined in each properties. Was not found in '$property'.'");
        }

        $min = null;

        foreach($constraints as $constraintKey=>$constraintValue)
        {
            if(!in_array($constraintKey, $authorizedKeys))
            {
                throw new DataSchemaUnexpectedKeyException();
            }

            if(($constraintKey === $authorizedKeys[0] || $constraintKey === $authorizedKeys[1]) && !is_bool($constraintValue))
            {
                throw new DataSchemaUnexpectedValueException("'$constraintKey' must be of type boolean.");
            }

            if( $constraintKey === $authorizedKeys[5] ||
                $constraintKey === $authorizedKeys[7] ||
                $constraintKey === $authorizedKeys[9])
            {
                $min = $constraintValue;
            }

            if( $constraintKey === $authorizedKeys[4] ||
                $constraintKey === $authorizedKeys[6] ||
                $constraintKey === $authorizedKeys[8])
            {
                if(!empty($min))
                {
                    if($min > $constraintValue)
                    {
                        throw new DataSchemaUnexpectedValueException("'$constraintKey' of property '$property' must be lesser than $min.");
                    }
                    $min = null;
                }
            }

            if($constraintKey === $authorizedKeys[10])
            {
                if(!is_array($constraintValue))
                {
                    throw new DataSchemaInvalidValueTypeException("Mimetype constraints value must be of type array. Invalid value found in '$property'.");
                }

                foreach($constraintValue as $mimetype)
                {
                    if(!in_array($mimetype, AuthorizedKeys::MIME_TYPES))
                    {
                        throw new DataSchemaUnexpectedValueException("Unexpected mimetype constraint for '$property'.");
                    }
                }
            }
        }
    }

    private function validateArray(string $property, $array)
    {
        if($array !== true)
        {
            throw new DataSchemaUnexpectedValueException("'array' key of a property must be equal to 'true' if defined. ".json_encode($array)." found in '$property'.");
        }
    }
}