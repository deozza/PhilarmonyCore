<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyCoreBundle\Exceptions\SchemaConfigFileBadlyFormated;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity\EntitySchema;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity\ValidateEntity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\PropertySchema;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\ValidateProperty;
use Symfony\Component\Yaml\Yaml;

class DatabaseSchemaValidator
{

    public function __construct(DatabaseSchemaLoader $schemaLoader)
    {
        $this->schemaLoader = $schemaLoader;
        $this->entities     = $this->schemaLoader->loadEntityEnumeration();
        $this->properties   = $this->schemaLoader->loadPropertyEnumeration();
        $this->enumerations = $this->schemaLoader->loadEnumerationEnumeration();
        $this->authorizedKeys = Yaml::parseFile(__DIR__."/authorizedKeys.yaml");
    }

    public function validateEntities()
    {
        if(empty($this->entities))
        {
            throw new SchemaConfigFileBadlyFormated($this->authorizedKeys['entity_head']." config file is empty.");
        }
        if(!array_key_exists($this->authorizedKeys['entity_head'], $this->entities))
        {
            throw new SchemaConfigFileBadlyFormated($this->authorizedKeys['entity_head']." config file must start with the '".$this->authorizedKeys['entity_head']."' header.");
        }
        if(empty($this->entities[$this->authorizedKeys['entity_head']]))
        {
            throw new SchemaConfigFileBadlyFormated($this->authorizedKeys['entity_head']." config file does not contain a schema.");
        }

        foreach($this->entities[$this->authorizedKeys['entity_head']] as $schemaName=>$schemaData)
        {
            $entity = new EntitySchema();
            $entity->setEntityName($schemaName);
            $entity->setProperties([]);
            $entity->setStates([]);

            if(!empty($schemaData[$this->authorizedKeys['entity_keys'][0]]))
            {
                $entity->setProperties($schemaData[$this->authorizedKeys['entity_keys'][0]]);
            }

            if(!empty($schemaData[$this->authorizedKeys['entity_keys'][1]]))
            {
                $entity->setStates($schemaData[$this->authorizedKeys['entity_keys'][1]]);
            }

            if(!empty($schemaData[$this->authorizedKeys['entity_keys'][2]]))
            {
                $entity->setConstraints($schemaData[$this->authorizedKeys['entity_keys'][2]]);
            }

            $validateEntity = new ValidateEntity($entity, $this->properties[$this->authorizedKeys['property_head']], $this->authorizedKeys, $this->entities[$this->authorizedKeys['entity_head']]);
            $validateEntity->validateProperties();
            $validateEntity->validateStates();
            if(!empty($entity->getConstraints()))
            {
                $validateEntity->validateConstraints($entity->getConstraints());
            }
        }
    }

    public function validateProperties()
    {
        if(empty($this->properties))
        {
            throw new SchemaConfigFileBadlyFormated($this->authorizedKeys['property_head']." config file is empty.");
        }
        if(!array_key_exists($this->authorizedKeys['property_head'], $this->properties))
        {
            throw new SchemaConfigFileBadlyFormated($this->authorizedKeys['property_head']." config file must start with the '".$this->authorizedKeys['property_head']."' header.");
        }
        if(empty($this->properties[$this->authorizedKeys['property_head']]))
        {
            throw new SchemaConfigFileBadlyFormated($this->authorizedKeys['property_head']." config file does not contain a schema.");
        }

        foreach($this->properties[$this->authorizedKeys['property_head']] as $schemaName=>$schemaData)
        {
            $property = new PropertySchema();
            $property->setPropertyName($schemaName);
            $property->setConstraints([]);
            $property->setType('');

            if(!empty($schemaData[$this->authorizedKeys['property_keys'][0]]))
            {
                $property->setType($schemaData[$this->authorizedKeys['property_keys'][0]]);
            }

            if(!empty($schemaData[$this->authorizedKeys['property_keys'][1]]))
            {
                $property->setConstraints($schemaData[$this->authorizedKeys['property_keys'][1]]);
            }

            $validateProperty = new ValidateProperty($property, $this->enumerations[$this->authorizedKeys['enumeration_head']], $this->entities[$this->authorizedKeys['entity_head']], $this->authorizedKeys);
            $validateProperty->validateType();
            $validateProperty->validateConstraints();
        }
    }

}