<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaInvalidValueTypeException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedValueException;
use Deozza\PhilarmonyUtils\DataSchema\AuthorizedKeys;

trait ValidateEntityTrait
{
    private function validateEntity(string $entity, array $entityContent)
    {
        $authorizedKeys = AuthorizedKeys::ENTITY_KEYS;
        $keys = array_keys($entityContent);
        if(!in_array($authorizedKeys[0], $keys))
        {
            throw new DataSchemaMissingKeyException("An entity must contains a '".$authorizedKeys[0]."' key. None was found in '$entity'");
        }

        if(empty($this->properties) || !array_key_exists(AuthorizedKeys::PROPERTY_HEAD, $this->properties))
        {
            throw new DataSchemaMissingKeyException(sprintf(self::EMPTY_OR_BAD_HEAD_MSG, 'property', AuthorizedKeys::PROPERTY_HEAD));
        }

        if(count($this->properties) > 1 || empty($this->properties[$authorizedKeys[0]]))
        {
            throw new DataSchemaMissingKeyException("The properties of '$entity' are empty or are badly formated");
        }
        foreach($entityContent[$authorizedKeys[0]] as $property)
        {
            if(!array_key_exists($property, $this->properties[AuthorizedKeys::PROPERTY_HEAD]))
            {
                throw new DataSchemaMissingKeyException("'$property.' from '$entity' is missing in the property config file");
            }
        }

        foreach($keys as $key)
        {
            if(!in_array($key, $authorizedKeys))
            {
                throw new DataSchemaUnexpectedKeyException("Only authorized key of an entity are ".json_encode($authorizedKeys).". '$key' found in '$entity'.");
            }

            if($key === $authorizedKeys[1])
            {
                $this->validateEntityStates($entity, $entityContent[$key]);
            }
        }

    }

    private function validateEntityStates(string $entityName, array $states)
    {
        if(!array_key_exists(AuthorizedKeys::DEFAULT_STATE, $states))
        {
            throw new DataSchemaMissingKeyException("The '".AuthorizedKeys::DEFAULT_STATE."' state is missing in $entityName");
        }

        foreach($states as $state=>$stateContent)
        {
            $this->validateEntityState($entityName, $state, $stateContent);
        }
    }

    private function validateEntityState(string $entityName, string $stateName, array $state)
    {
        $authorizedKeys = AuthorizedKeys::STATE_KEYS;
        $keys = array_keys($state);

        foreach($keys as $key)
        {
            switch($key)
            {
                case $authorizedKeys[0]: $this->validateEntityStateMethods($entityName, $stateName, $state[$key]);;
                    break;
                case $authorizedKeys[1]: $this->validateEntityStateConstraints($entityName, $stateName, $state[$key]);
                    break;
                default : throw new DataSchemaUnexpectedKeyException("The authorized keys in an entity state are ".json_encode($authorizedKeys).". '$key' found in '$entityName'.");
                    break;
            }
        }
    }

    private function validateEntityStateConstraints(string $entity, string $state, $constraints)
    {
        if(!is_array($constraints))
        {
            throw new DataSchemaUnexpectedValueException("Constraints of an entity must be of type 'array'. Unexpected value found in state '$state' of '$entity'.");
        }

        foreach($constraints as $constraintKey=>$constraintValue)
        {
            $this->validateEntityStateConstraint($entity, $state, $constraintKey,$constraintValue);
        }
    }

    private function validateEntityStateConstraint(string $entity, string $state, string $constraintKey, array $constraints)
    {
        $authorizedKeys = AuthorizedKeys::ENTITY_CONSTRAINT_KEYS;
        $key = explode('.',$constraintKey);
        switch($key[0])
        {
            case $authorizedKeys[0]: $this->checkManualConstraint($entity, $state, $constraints);
                break;
            case $authorizedKeys[1] : {
                foreach($constraints as $constraintKey=>$constraints)
                {
                    $this->checkPropertyConstraint($entity, $state, $constraintKey, $constraints);
                }
            }
                break;
            default : throw new DataSchemaUnexpectedKeyException("Authorized constraints keys are ".json_encode($authorizedKeys).". Unexpected '$constraintKey' found in state '$state' of '$entity'.");
                break;
        }
    }

    private function checkManualConstraint(string $entity, string $state, array $constraints)
    {
        $authorizedKeys = AuthorizedKeys::BY_KEYS;
        if(!array_key_exists('by', $constraints))
        {
            throw new DataSchemaMissingKeyException();
        }

        if(!is_array($constraints['by']))
        {
            throw new DataSchemaInvalidValueTypeException();
        }

        foreach($constraints['by'] as $key=>$value)
        {
            if(!in_array($key, $authorizedKeys))
            {
                throw new DataSchemaUnexpectedKeyException();
            }

            if(!is_array($value))
            {
                throw new DataSchemaInvalidValueTypeException();
            }
        }
    }

    private function checkPropertyConstraint(string $entity, string $state, string $property, array $constraints)
    {
        $authorizedKeys = AuthorizedKeys::ENTITY_CONSTRAINT;
        $propertyExploded = explode('.', $property);
        $propertiesAvailable = $this->entities[AuthorizedKeys::ENTITY_HEAD][$entity][AuthorizedKeys::ENTITY_KEYS[0]];

        if(!in_array($propertyExploded[0], $propertiesAvailable))
        {
            throw new DataSchemaUnexpectedValueException();
        }

        foreach($constraints as $constraint)
        {
            $regex = "/(\w+\.)/";
            preg_match($regex, $constraint, $matches);

            if(!empty($matches))
            {
                $operator = substr($matches[1], 0, strlen($matches[1]) - 1);
                if(!in_array($operator, $authorizedKeys))
                {
                    throw new DataSchemaUnexpectedValueException();
                }
                $regex = "/(\w+\()/";
                preg_match($regex, $constraint, $matches);
                if(!empty($matches))
                {
                    $constraintOnEntity = substr($matches[1], 0, strlen($matches[1]) - 1);
                    switch($constraintOnEntity)
                    {
                        case "self" : {
                            $regex = "/(\(.+\))/";
                            preg_match($regex, $constraint, $matches);
                            if(!empty($matches))
                            {
                                $property = substr($matches[1], 1, strlen($matches[1])-2);

                                $exists = $this->schemaLoader->propertyFinder($property, $entity);

                                if($exists === false)
                                {
                                    throw new DataSchemaUnexpectedValueException($property);
                                }
                            }
                        };
                            break;
                        case "value" : {

                        }
                            break;
                        default : {
                            if(!in_array($constraintOnEntity, array_keys($this->entities[AuthorizedKeys::ENTITY_HEAD])))
                            {
                                throw new DataSchemaUnexpectedValueException($constraintOnEntity);
                            }
                            $regex = "/(\(.+\))/";
                            preg_match($regex, $constraint, $matches);
                            if(!empty($matches))
                            {
                                $propertiesAvailable = $this->entities[AuthorizedKeys::ENTITY_HEAD][$entity]['properties'];
                                $properties = substr($matches[1], 1, strlen($matches[1])-2);
                                $explodedProperties = explode(",",$properties);
                                foreach($explodedProperties as $explodedProperty)
                                {
                                    if(!in_array($explodedProperty, $propertiesAvailable))
                                    {
                                        throw new DataSchemaUnexpectedValueException($explodedProperty);
                                    }
                                }
                            }
                        }
                            break;
                    }
                }

            }
            elseif(strpos($constraint, "equal") == false)
            {
                $regex = "/(\w+\()/";
                preg_match($regex, $constraint, $matches);
                $operator = substr($matches[1], 0, strlen($matches[1]) - 1);
                if(!in_array($operator, $authorizedKeys))
                {
                    throw new DataSchemaUnexpectedValueException();
                }
            }

        }
    }

    private function validateEntityStateMethods(string $entityName, string $stateName, $methods)
    {
        if(!is_array($methods))
        {
            throw new DataSchemaUnexpectedValueException("Methods of an entity must be of type 'array'. Unexpected value found in state '$stateName' of '$entityName'.");
        }

        $authorizedMethod = AuthorizedKeys::METHODS;
        foreach($methods as $method => $methodContent)
        {
            if(!in_array($method, $authorizedMethod))
            {
                throw new DataSchemaUnexpectedKeyException("The authorized methods for a state are ".json_encode($authorizedMethod).". '$method' found in the '$stateName' of '$entityName'.");
            }
            $this->checkKeysOfMethod($method, $methodContent, $entityName);
        }
    }

    private function checkKeysOfMethod(string $method, array $content, string $entityName)
    {
        $authorizedKeys = AuthorizedKeys::METHOD_KEYS;
        $keys = array_keys($content);
        foreach($keys as $key)
        {
            if(!in_array($key, $authorizedKeys))
            {
                throw new DataSchemaUnexpectedKeyException("Authorized keys for a method are ".json_encode($authorizedKeys).". '$key' found in '$entityName'");
            }
        }

        if(!in_array($authorizedKeys[1], $keys))
        {
            throw new DataSchemaMissingKeyException("A method must contains the '".$authorizedKeys[1]."' key.");
        }

        $functionName = 'validateMethod'.$method;
        $this->{$functionName}($content, $entityName);

        if(in_array($authorizedKeys[2], $keys))
        {
            $this->validatePostScriptOfMethod($method, $content[$authorizedKeys[2]], $entityName);
        }
    }

    private function validateMethodPOST(array $content, string $entityName)
    {
        $this->validatePropertiesOFMethod($content, $entityName);
        $this->validateByOfMethod($content);
    }

    private function validateMethodGET(array $content)
    {
        $this->validateByOfMethod($content);
    }

    private function validateMethodPATCH(array $content, string $entityName)
    {
        $this->validatePropertiesOFMethod($content, $entityName);
        $this->validateByOfMethod($content);
    }

    private function validateMethodDELETE(array $content)
    {
        $this->validateByOfMethod($content);
    }

    private function validatePropertiesOFMethod($content, $entityName)
    {
        if(!array_key_exists(AuthorizedKeys::METHOD_KEYS[0], $content))
        {
            throw new DataSchemaMissingKeyException("'POST' and 'PATCH' methods must contain a '".AuthorizedKeys::METHOD_KEYS[0]."' key. None waa found in '$entityName'.");
        }

        if(is_string($content[AuthorizedKeys::METHOD_KEYS[0]]) && $content[AuthorizedKeys::METHOD_KEYS[0]] !== 'all')
        {
            throw new DataSchemaUnexpectedKeyException();
        }
        elseif(is_array($content[AuthorizedKeys::METHOD_KEYS[0]]))
        {
            foreach($content[AuthorizedKeys::METHOD_KEYS[0]] as $property)
            {
                if(!in_array($property, $this->entities[AuthorizedKeys::ENTITY_HEAD][$entityName][AuthorizedKeys::METHOD_KEYS[0]]))
                {
                    throw new DataSchemaUnexpectedKeyException();
                }
            }
        }
    }

    private function validateByOfMethod($content)
    {
        if(is_string($content[AuthorizedKeys::METHOD_KEYS[1]]) && $content[AuthorizedKeys::METHOD_KEYS[1]] !== 'all')
        {
            throw new DataSchemaUnexpectedKeyException("The '".AuthorizedKeys::METHOD_KEYS[1]."' key of a method must be an array or equal to 'all'. '".$content[AuthorizedKeys::METHOD_KEYS[1]]."' found.");
        }
        elseif(is_array($content[AuthorizedKeys::METHOD_KEYS[1]]))
        {
            $authorizedBy = AuthorizedKeys::BY_KEYS;
            foreach($content[AuthorizedKeys::METHOD_KEYS[1]] as $by=>$byContent)
            {
                if(!in_array($by, $authorizedBy))
                {
                    throw new DataSchemaUnexpectedKeyException("The '".AuthorizedKeys::METHOD_KEYS[1]."' key accepts only ".json_encode($authorizedBy)." as value. '$by' found.");
                }

                if(!is_array($byContent))
                {
                    throw new DataSchemaUnexpectedKeyException("The '".AuthorizedKeys::METHOD_KEYS[1]."' key of a method must be an array or equal to 'all'. '$byContent' found.");
                }
            }
        }
    }

    private function validatePostScriptOfMethod(string $method, $content, string $entityName)
    {
        if(empty($content) || !is_array($content))
        {
            throw new DataSchemaUnexpectedValueException("'".AuthorizedKeys::METHOD_KEYS[2]."' found in the '$method' method of '$entityName' must not be empty and of type 'array'");
        }
    }
}