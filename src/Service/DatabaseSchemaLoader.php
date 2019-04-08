<?php
namespace Deozza\PhilarmonyBundle\Service;

use Deozza\PhilarmonyBundle\Entity\EntityJoinPost;
use Deozza\PhilarmonyBundle\Entity\EntityPost;
use Deozza\PhilarmonyBundle\Entity\PropertyPost;
use Deozza\PhilarmonyBundle\Entity\TypePost;
use Symfony\Component\Yaml\Yaml;

class DatabaseSchemaLoader
{

    public function __construct(string $entity, string $property, string $enumeration, string $path)
    {
        $this->entityPath = $entity;
        $this->propertyPath = $property;
        $this->enumerationPath = $enumeration;
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

        foreach (array_keys($values) as $key)
        {
            if($key == strtoupper($entity_name))
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values[$key];
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

        foreach (array_keys($values) as $key)
        {
            if($key == strtoupper($property_name))
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values[$key];
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

        foreach (array_keys($values) as $key)
        {
            if($key == strtoupper($enumeration_name))
            {
                if($returnKey)
                {
                    return $key;
                }
                return $values[$key];
            }
        }

        return null;
    }
}