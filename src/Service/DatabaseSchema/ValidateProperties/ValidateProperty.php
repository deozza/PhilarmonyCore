<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\PropertySchema;

class ValidateProperty
{
    private $property;

    public function __construct(PropertySchema $property, array $enumerationsSchema, array $entitiesSchema, array $authorizedKeys)
    {
        $this->property = $property;
        $this->enumerationsSchema = $enumerationsSchema;
        $this->entitiesSchema = $entitiesSchema;

        $this->authorizedKeys = $authorizedKeys;
    }

    public function validateType()
    {
        $explodedType = explode('.', $this->property->getType());
        $this->checkArrayContains($explodedType[0], $this->authorizedKeys['types'], "Available types are ".json_encode($this->authorizedKeys['types']).".\nInvalid type found in property '".$this->property->getPropertyName()."'.");

        if(in_array($explodedType[0], ['embedded', 'entity', 'enumeration']))
        {
            if(count($explodedType) < 2)
            {
                throw new \Exception('When you declare a property with one of the type '.json_encode(['embedded', 'entity', 'enumeration']).", you must point it to a target. Not target was found for the property '".$this->property->getPropertyName()."'.");
            }

            $functionName = 'validate'.ucfirst($explodedType[0]);
            $this->{$functionName}($explodedType[1]);
        }
    }

    public function validateConstraints()
    {
        $this->checkKeyExist($this->authorizedKeys['property_constraint_operators'][0], $this->property->getConstraints(), "A property must contain a ".$this->authorizedKeys['property_constraint_operators'][0]." constraint. It was not found in '".$this->property->getPropertyName()."'.");
        $this->checkKeyExist($this->authorizedKeys['property_constraint_operators'][1], $this->property->getConstraints(), "A property must contain a ".$this->authorizedKeys['property_constraint_operators'][1]." constraint. It was not found in '".$this->property->getPropertyName()."'.");
        $propertyConstraints = array_merge($this->authorizedKeys['property_constraint_operators'] ,$this->authorizedKeys['basic_constraint_operators']);
        $minIsDefined = null;
        foreach($this->property->getConstraints() as $constraintName=>$constraintData)
        {
            $this->checkArrayContains($constraintName, $propertyConstraints, "Valid property constraints are ".json_encode($propertyConstraints)." Invalid '$constraintName' found in property '".$this->property->getPropertyName()."'.");
            if(in_array($constraintName, ['lt', 'ltoe', 'lengthMin']))
            {
                $minIsDefined = $constraintData;
            }
            if(in_array($constraintName, ['gt', 'gtoe', 'lengthMax']) && !empty($minIsDefined))
            {
                if($minIsDefined >= $constraintData)
                {
                    throw new \Exception("'$constraintName' of property '".$this->property->getPropertyName()."' must be lesser than $minIsDefined.");
                }
            }
            if($constraintName === "mime")
            {
                if(!is_array($constraintData))
                {
                    throw new \Exception("A $constraintName constraint must be an array. Invalid constraint found in property '".$this->property->getPropertyName()."'.");
                }
                $this->validateMimeType($constraintData);
            }
        }
    }

    private function validateMimeType(array $mimetypes)
    {
        foreach($mimetypes as $mimetype)
        {
            $this->checkArrayContains($mimetype, $this->authorizedKeys['mime_types'], "Authorized mimetypes are ".json_encode($this->authorizedKeys['mime_types']).". Invalid type '$mimetype' found in '".$this->property->getPropertyName()."'.");
        }
    }

    private function validateEmbedded(string $name)
    {
        $this->checkKeyExist($name, $this->entitiesSchema, "Entity '$name' was not found in the entity config file as an embedded entity.\nDeclared in the property '".$this->property->getPropertyName()."'.");
        if(array_key_exists($this->authorizedKeys['entity_keys'][1], $this->entitiesSchema[$name]))
        {
            throw new \Exception("Entity $name contains a state and can not be an embedded entity.\nDeclared in the property '".$this->property->getPropertyName()."'.");
        }
    }

    private function validateEntity(string $name)
    {
        $this->checkKeyExist($name, $this->entitiesSchema, "Entity '$name' was not found in the entity config file as an entity.\nDeclared in the property '".$this->property->getPropertyName()."'.");
        if(!array_key_exists($this->authorizedKeys['entity_keys'][1], $this->entitiesSchema[$name]))
        {
            throw new \Exception("Entity $name does not contain a state and can not be an joined entity.\nDeclared in the property '".$this->property->getPropertyName()."'.");
        }
    }

    private function validateEnumeration(string $name)
    {
        $this->checkKeyExist($name, $this->enumerationsSchema, "Enumeration '$name' was not found in the enumeration config file.\nDeclared in the property '".$this->property->getPropertyName()."'.");
    }

    private function checkKeyExist(string $key, array $schema, string $message)
    {
        if(!array_key_exists($key,$schema))
        {
            throw new \Exception($message);
        }
    }

    private function checkArrayContains(string $key, array $schema, string $message)
    {
        if(!in_array($key,$schema))
        {
            throw new \Exception($message);
        }
    }
}