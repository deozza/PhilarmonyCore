<?php
namespace Deozza\PhilarmonyBundle\Service\DatabaseSchema;

use Symfony\Component\Yaml\Yaml;

class DatabaseSchemaLoader
{

    public function __construct(string $entity, string $property, string $enumeration, string $validation, string $path)
    {
        $this->entityPath = $entity;
        $this->propertyPath = $property;
        $this->enumerationPath = $enumeration;
        $this->validationPath = $validation;
        $this->rootPath = $path;
    }

    public function loadEntityEnumeration($entity_name = null, $returnKey = false)
    {
        $entities = file_get_contents($this->rootPath.$this->entityPath.".yaml");

        try
        {
            $values = Yaml::parse($entities);
        }
        catch(\Exception $e)
        {
            return null;
        }

        if(empty($entity_name))
        {
            return $values;
        }

        foreach (array_keys($values['entities']) as $key)
        {
            if($key == $entity_name)
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values['entities'][$key];
            }
        }

        return null;
    }

    public function loadPropertyEnumeration($property_name = null, $returnKey = false)
    {
        $properties = file_get_contents($this->rootPath.$this->propertyPath.".yaml");

        try
        {
            $values = Yaml::parse($properties);
        }
        catch(\Exception $e)
        {
            return null;
        }
        if(empty($property_name))
        {
            return $values;
        }

        foreach (array_keys($values['properties']) as $key)
        {
            if($key == $property_name)
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values['properties'][$key];
            }
        }

        return null;
    }

    public function loadEnumerationEnumeration($enumeration_name = null, $returnKey = false)
    {
        $enumerations = file_get_contents($this->rootPath.$this->enumerationPath.".yaml");
        try
        {
            $values = Yaml::parse($enumerations);
        }
        catch(\Exception $e)
        {
            return null;
        }
        if(empty($enumeration_name))
        {
            return $values;
        }

        foreach (array_keys($values['enumerations']) as $key)
        {
            if($key == $enumeration_name)
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values['enumerations'][$key];
            }
        }

        return null;
    }

    public function loadValidationEnumeration($validation_name = null, $returnKey = false)
    {
        $validation = file_get_contents($this->rootPath.$this->validationPath.".yaml");
        try
        {
            $values = Yaml::parse($validation);
        }
        catch(\Exception $e)
        {
            return null;
        }
        if(empty($validation_name))
        {
            return $values;
        }

        foreach (array_keys($values['validations']) as $key)
        {
            if($key == $validation_name)
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values['validations'][$key];
            }
        }

        return null;
    }
}