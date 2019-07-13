<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedKeyException;

trait ValidateEntityTrait
{
    private function validateEntity(string $entity, array $entityContent)
    {
        $authorizedKeys = ['properties', 'states', 'constraints'];
        $keys = array_keys($entityContent);
        if(!in_array('properties', $keys))
        {
            throw new DataSchemaMissingKeyException("An entity must contains a 'properties' key. None was found in '$entity'");
        }

        if(empty($this->properties) || !array_key_exists(self::PROPERTY_HEAD, $this->properties))
        {
            throw new DataSchemaMissingKeyException(sprintf(self::EMPTY_OR_BAD_HEAD_MSG, 'property', self::PROPERTY_HEAD));
        }

        if(count($this->properties) > 1 || empty($this->properties['properties']))
        {
            throw new DataSchemaMissingKeyException("The properties of '$entity' are empty or are badly formated");
        }
        foreach($entityContent['properties'] as $property)
        {
            if(!array_key_exists($property, $this->properties['properties']))
            {
                throw new DataSchemaMissingKeyException("'$property.' from '$entity' is missing in the property config file");
            }
        }

        foreach($keys as $key)
        {
            if(!in_array($key, $authorizedKeys))
            {
                throw new DataSchemaUnexpectedKeyException("Only authorized key of an entity are 'properties', 'states' and 'constraints'. '$key' found in '$entity'.");
            }

            if($key === $authorizedKeys[1])
            {
                $this->validateEntityStates($entity, $entityContent[$key]);
            }
        }

    }

    private function validateEntityStates(string $entityName, array $states)
    {
        if(!array_key_exists('__default', $states))
        {
            throw new DataSchemaMissingKeyException("The '__default' state is missing in $entityName");
        }

        foreach($states as $state=>$stateContent)
        {
            $this->validateEntityState($entityName, $state, $stateContent);
        }
    }

    private function validateEntityState(string $entityName, string $stateName, array $state)
    {
        $authorizedKeys = ['methods', 'constraints'];
        $keys = array_keys($state);

        foreach($keys as $key)
        {
            if(!in_array($key, $authorizedKeys))
            {
                throw new DataSchemaUnexpectedKeyException("The authorized keys in an entity state are 'methods' and 'constraints'. '$key' found in '$entityName'.");
            }

            if($key === $authorizedKeys[0])
            {
                $this->validateEntityStateMethods($entityName, $stateName, $state[$key]);
            }
        }
    }

    private function validateEntityStateMethods(string $entityName, string $stateName, array $methods)
    {
        $authorizedMethod = ['POST', 'GET', 'PATCH', 'DELETE'];
        foreach($methods as $method => $methodContent)
        {
            if(!in_array($method, $authorizedMethod))
            {
                throw new DataSchemaUnexpectedKeyException("The authorized methods for a state are 'POST', 'GET', 'PATCH' and 'DELETE'. '$method' found in the '$stateName' of '$entityName'.");
            }

            $functionName = 'validateMethod'.$method;
            $this->{$functionName}($methodContent, $entityName);
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
        if(!array_key_exists('properties', $content))
        {
            throw new DataSchemaMissingKeyException("'POST' and 'PATCH' methods must contain a 'properties' key. None waa found in '$entityName'.");
        }

        if(is_string($content['properties']) && $content['properties'] !== 'all')
        {
            throw new DataSchemaUnexpectedKeyException();
        }
        elseif(is_array($content['properties']))
        {
            foreach($content['properties'] as $property)
            {
                if(!in_array($property, $this->entities[self::ENTITY_HEAD][$entityName]['properties']))
                {
                    throw new DataSchemaUnexpectedKeyException();
                }
            }
        }
    }

    private function validateByOfMethod($content)
    {
        if(!array_key_exists('by', $content))
        {
            throw new DataSchemaMissingKeyException("A method must contains the 'by' key.");
        }

        if(is_string($content['by']) && $content['by'] !== 'all')
        {
            throw new DataSchemaUnexpectedKeyException("The 'by' key of a method must be an array or equal to 'all'. '".$content['by']."' found.");
        }
        elseif(is_array($content['by']))
        {
            $authorizedBy = ['users', 'roles'];
            foreach($content['by'] as $by=>$byContent)
            {
                if(!in_array($by, $authorizedBy))
                {
                    throw new DataSchemaUnexpectedKeyException("The 'by' key accepts only 'users' and 'roles' as value. '$by' found.");
                }

                if(!is_array($byContent))
                {
                    throw new DataSchemaUnexpectedKeyException("The 'by' key of a method must be an array or equal to 'all'. '$byContent' found.");
                }
            }
        }
    }
}