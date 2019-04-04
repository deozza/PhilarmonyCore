<?php
namespace Deozza\PhilarmonyBundle\Service;


use Deozza\PhilarmonyBundle\Entity\EntityPost;
use Deozza\PhilarmonyBundle\Entity\PropertyPost;
use Deozza\PhilarmonyBundle\Entity\TypePost;

class DatabaseSchemaLoader
{

    public function __construct(string $entity, string $property, string $type, string $path)
    {
        $this->rootPath = $path;
        $this->entityPath = $entity;
        $this->propertyPath = $property;
        $this->typePath = $type;

    }

    public function loadEntityEnumeration($entity_name = null, $returnKey = false)
    {
        $entities = json_decode(file_get_contents($this->rootPath.$this->entityPath.".json"), true);

        if(empty($entity_name))
        {
            return $entities;
        }

        foreach (array_keys($entities) as $key)
        {
            if($key == strtoupper($entity_name))
            {
                if($returnKey)
                {
                    return $key;
                }
                return $entities[$key];
            }
        }

        return null;
    }

    public function loadPropertyEnumeration($property_name = null, $returnKey = false)
    {
        $properties = json_decode(file_get_contents($this->rootPath.$this->propertyPath.".json"), true);

        if(empty($property_name))
        {
            return $properties;
        }

        foreach (array_keys($properties) as $key)
        {
            if($key == strtoupper($property_name))
            {
                if($returnKey)
                {
                    return $key;
                }
                return $properties[$key];
            }
        }

        return null;
    }

    public function loadTypeEnumeration($type_name = null, $returnKey = false)
    {
        $types = json_decode(file_get_contents($this->rootPath.$this->typePath.".json"), true);

        if(empty($type_name))
        {
            return $types;
        }

        foreach (array_keys($types) as $key)
        {
            if($key == strtoupper($type_name))
            {
                if($returnKey)
                {
                    return $key;
                }
                return $types[$key];
            }
        }

        return null;
    }

    public function pushEntityEnumeration(EntityPost $entityPost)
    {
        $file = __DIR__ . "/../../config/Philarmony/entity.json";
        $entities = json_decode(file_get_contents($file), true);
        $entities[strtoupper($entityPost->getName())] = $entityPost->getProperties();
        try
        {
            file_put_contents($file, json_encode($entities));
            return $entities;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function pushPropertyEnumeration(PropertyPost $propertyPost)
    {
        $file = __DIR__ . "/../../config/Philarmony/property.json";
        $properties = json_decode(file_get_contents($file), true);
        $properties[strtoupper($propertyPost->getName())] =
            [
                "TYPE"=>$propertyPost->getType(),
                "IS_REQUIRED"=>$propertyPost->getisRequired(),
                "UNIQUE" =>$propertyPost->getUnique()
            ];

        try
        {
            file_put_contents($file, json_encode($properties));
            return $properties;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function pushTypeEnumeration(TypePost $typePost)
    {
        try
        {
            preg_match($typePost->getRegex(), "");
        }
        catch(\Exception $e)
        {
            return false;
        }

        $file = __DIR__ . "/../../config/Philarmony/type.json";
        $types = json_decode(file_get_contents($file), true);
        $types[strtoupper($typePost->getName())] =
            [
                "REGEX"=>$typePost->getRegex(),
            ];

        try
        {
            file_put_contents($file, json_encode($types));
            return $types;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }
}