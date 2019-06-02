<?php
namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyUtils\Exceptions\FileNotFound;
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
            throw new FileNotFound();
        }

        if(!isset($values['entities']))
        {
            throw new FileNotFound("Root node of ".$this->rootPath.$this->entityPath.".yaml must be 'entities'.");
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
            throw new FileNotFound();
        }

        if(!isset($values['properties']))
        {
            throw new FileNotFound("Root node of ".$this->rootPath.$this->propertyPath.".yaml must be 'properties'.");
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
        $enumerations = file_get_contents($this->rootPath . $this->enumerationPath . ".yaml");

        try
        {
            $values = Yaml::parse($enumerations);
        }
        catch (\Exception $e)
        {
            throw new FileNotFound();
        }

        if(!isset($values['enumerations']))
        {
            throw new FileNotFound("Root node of ".$this->rootPath.$this->enumerationPath.".yaml must be 'enumeration'.");
        }
        if (empty($enumeration_name)) {
            return $values;
        }

        foreach (array_keys($values['enumerations']) as $key) {
            if ($key == $enumeration_name) {
                if ($returnKey) {
                    return $key;
                }
                return $values['enumerations'][$key];
            }
        }

        return null;
    }
}