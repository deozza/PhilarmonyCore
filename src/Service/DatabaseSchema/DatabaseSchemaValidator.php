<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;

use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaEmptyOrHeadMissingException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaMissingKeyException;
use Deozza\PhilarmonyCoreBundle\Exceptions\DataSchemaUnexpectedKeyException;

class DatabaseSchemaValidator
{
    const ENTITY_HEAD = "entities";
    const PROPERTY_HEAD = "properties";
    const ENUM_HEAD = "enumerations";

    const ENTITY_EXPECTED_KEY0 = ['properties', 'states', 'constraints'];
    const ENTITY_EXPECTED_KEY_STATES_DEFAULT = '__default';
    const ENTITY_EXPECTED_KEY_STATES = ['methods', 'constraints'];
    const ENTITY_EXPECTED_METHODS = ['POST', 'GET', 'PATCH', 'DELETE'];
    const ENTITY_EXPECTED_KEY_METHODS = ['by'];
    const ENTITY_EXPECTED_BY = ['users', 'roles'];

    const EMPTY_OR_BAD_HEAD_MSG = "The %s schema is empty or does not start with '%s' key.";
    const UNEXPECTED_KEY_MSG = "Unexpected key '%s' found in entity '%s'.";
    const UNEXPECTED_KEY__SUB_MSG = "Unexpected key '%s' found in '%s' of entity '%s'.";

    const E_UNEXPECTED_VALUE_MSG = "'%s' value in entity '%s' must be of type %s.";
    const E_MISSING_KEY_MSG = "'%s' key was expected inside '%s' of entity '%s'. It was not found.";

    public function __construct(DatabaseSchemaLoader $schemaLoader)
    {
        $this->schemaLoader = $schemaLoader;
    }

    public function validateEntity()
    {
        $entities = $this->schemaLoader->loadEntityEnumeration();
        $entities = $entities[self::ENTITY_HEAD];
        foreach($entities as $entityName => $entityContent)
        {
            foreach($entityContent as $level0Key => $level0Content)
            {
                if(!$this->checkKeyIsValid(self::ENTITY_EXPECTED_KEY0, $level0Key))
                {
                    throw new DataSchemaUnexpectedKeyException(sprintf(self::UNEXPECTED_KEY_MSG, $level0Key, $entityName));
                }

                if($level0Key === self::ENTITY_EXPECTED_KEY0[0])
                {
                    if(!is_array($level0Content))
                    {
                        throw new DataSchemaUnexpectedKeyException(sprintf(self::E_UNEXPECTED_VALUE_MSG, $level0Key, $entityName, 'array'));
                    }
                }

                if($level0Key === self::ENTITY_EXPECTED_KEY0[1])
                {
                    if(!is_array($level0Content))
                    {
                        throw new DataSchemaUnexpectedKeyException(sprintf(self::E_UNEXPECTED_VALUE_MSG, $level0Key, $entityName, 'array'));
                    }

                    if(in_array(self::ENTITY_EXPECTED_KEY_STATES_DEFAULT, $level0Content))
                    {
                        throw new DataSchemaMissingKeyException(sprint(self::E_MISSING_KEY_MSG, self::ENTITY_EXPECTED_KEY_STATES_DEFAULT, $level0Key, $entityName));
                    }

                    foreach($level0Content as $state => $content)
                    {
                        $this->validateArray($content, $entityName, $state, self::ENTITY_EXPECTED_KEY_STATES);
                    }

                }
            }
        }
        die;
    }

    public function validateProperty()
    {

    }

    public function validateEnumeration()
    {

    }

    private function isEmptyOrWithoutHead(?array $schema, string $head)
    {
        if(empty($schema))
        {
            throw new DataSchemaEmptyOrHeadMissingException(sprintf(self::EMPTY_OR_BAD_HEAD_MSG, $head, $head));
        }

        if(!array_key_exists($head, $schema))
        {
            throw new DataSchemaEmptyOrHeadMissingException(sprintf(self::EMPTY_OR_BAD_HEAD_MSG, $head, $head));
        }
    }

    private function checkKeyIsValid($expectedKey, string $submitedKey)
    {
        return in_array($submitedKey, $expectedKey);
    }

    private function validateArray(array $array, string $entityName, string $arrayName, array $authorizedKeys = null, array $expectedKeys = null )
    {
        if(!empty($authorizedKeys))
        {
            foreach($array as $key => $value)
            {
                if(!$this->checkKeyIsValid($authorizedKeys, $key))
                {
                    throw new DataSchemaUnexpectedKeyException(sprintf(self::UNEXPECTED_KEY_MSG, $key, $entityName));
                }
            }
        }

        if(!empty($expectedKeys))
        {
            foreach($expectedKeys as $key)
            {
                if(!array_key_exists($key, $array))
                {
                    throw new DataSchemaMissingKeyException(sprintf(self::E_MISSING_KEY_MSG, $key, $arrayName, $entityName));
                }
            }
        }

        foreach($array as $subArrayName => $subArrayContent)
        {
            if(is_array($subArrayContent))
            {
                $this->goToNextArray($subArrayName, $subArrayContent, $entityName);
            }
            elseif($subArrayContent != 'all')
            {
                throw new DataSchemaUnexpectedKeyException(sprintf(self::UNEXPECTED_KEY_MSG, $subArrayContent, $entityName));
            }
        }
    }

    private function goToNextArray(string $arrayName, array $arrayContent, string $entityName)
    {
        if($arrayName === "methods")
        {
            $this->validateArray($arrayContent, $entityName, $arrayName, self::ENTITY_EXPECTED_METHODS);
        }
        elseif(in_array($arrayName, self::ENTITY_EXPECTED_METHODS))
        {
            $expectedKeys = self::ENTITY_EXPECTED_KEY_METHODS;
            if($arrayName !== "GET") $expectedKeys[] = "properties";

            $this->validateArray($arrayContent, $entityName, $arrayName,null, $expectedKeys);
        }
        elseif(in_array($arrayName, self::ENTITY_EXPECTED_KEY_METHODS))
        {
            $this->validateArray($arrayContent, $entityName, $arrayName,self::ENTITY_EXPECTED_BY);
        }
    }

}