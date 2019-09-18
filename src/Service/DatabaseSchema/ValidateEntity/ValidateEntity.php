<?php


namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity;

class ValidateEntity
{
    private $entity;

    public function __construct(EntitySchema $entity, array $propertiesSchema, array $authorizedKeys)
    {
        $this->entity = $entity;
        $this->propertiesSchema = $propertiesSchema;
        $this->authorizedKeys = $authorizedKeys;
    }

    public function validateProperties()
    {
        if(empty($this->entity->getProperties()))
        {
            throw new \Exception($this->entity->getEntityName()." does not contain properties.");
        }

        foreach($this->entity->getProperties() as $property)
        {
            $this->checkKeyExist($property, $this->propertiesSchema[$this->authorizedKeys['property_head']], 'property does not exist');
        }
    }

    public function validateStates()
    {
        if(empty($this->entity->getStates())) return ;

        $this->checkKeyExist($this->authorizedKeys['default_state'], $this->entity->getStates(),'__default state missing');

        foreach($this->entity->getStates() as $stateName=>$stateData)
        {
            $containsMethod = false;
            foreach($stateData as $stateKey=>$stateKeyData)
            {
                switch($stateKey)
                {
                    case $this->authorizedKeys['state_keys'][0]:
                    {
                        $containsMethod = true;
                        foreach($stateKeyData as $methodName=>$methodData)
                        {
                            $this->validateMethod($methodName, $methodData);
                        }
                    }break;
                    case $this->authorizedKeys['state_keys'][1]:
                    {
                        if(!is_array($stateKeyData))
                        {
                            throw new \Exception("Constraints must be of type array");
                        }
                        $this->validateConstraints($stateKeyData);
                    }break;
                    default: throw new \Exception("extra key $stateKey in $stateName of ".$this->entity->getEntityName());break;
                }
            }
            if(!$containsMethod)
            {
                throw new \Exception("method must exist");
            }
        }
    }

    public function validateConstraints(array $constraints)
    {
        if(count($constraints)>1)
        {
            throw new \Exception("badly formated constraints for".$this->entity->getEntityName());
        }
        switch(array_key_first($constraints))
        {
            case $this->authorizedKeys['entity_constraint_keys'][0]:
            {
                $this->validateManualConstraint($constraints[$this->authorizedKeys['entity_constraint_keys'][0]]);
            }break;
            case $this->authorizedKeys['entity_constraint_keys'][1]:
            {
                $this->validatePropertiesConstraint($constraints[$this->authorizedKeys['entity_constraint_keys'][1]]);
            }break;
            default: throw new \Exception("badly formated constraints for ".$this->entity->getEntityName());break;
        }
    }

    private function validateMethod(string $methodName, array $methodData)
    {
        $this->checkArrayContains($methodName, $this->authorizedKeys['methods'], "$methodName does not exist");

        if($methodName === 'POST' || $methodName === 'PATCH')
        {
            $this->checkKeyExist($this->authorizedKeys['method_keys'][0], $methodData, 'form must contain properties');
            $this->validateFormProperties($methodData[$this->authorizedKeys['method_keys'][0]]);
        }

        $this->checkKeyExist($this->authorizedKeys['method_keys'][1], $methodData, 'method must contain by');
        $this->validateMethodBy($methodData[$this->authorizedKeys['method_keys'][1]]);
    }

    private function validateFormProperties(array $properties)
    {
        foreach($properties as $property)
        {
            $this->checkArrayContains($property, $this->entity->getProperties(), "$property does not exist in ".$this->entity->getEntityName());
        }
    }

    private function validateMethodBy($by)
    {
        if($by === "all")
        {
            return;
        }
        if(!is_array($by))
        {
            throw new \Exception("by must be an array");
        }
        foreach($by as $byKind=>$byData)
        {
            $this->checkArrayContains($byKind, $this->authorizedKeys['by_keys'], "$byKind does not exist");
        }
    }

    private function validateManualConstraint($constraint)
    {
        if(count($constraint) != 2)
        {
            throw new \Exception("badly formatted manual constraint");
        }

        foreach($this->authorizedKeys['manual_constraint_keys'] as $key)
        {
            $this->checkKeyExist($key, $constraint, "manual constraint must contain $key.");
            if($key === $this->authorizedKeys['manual_constraint_keys'][0])
            {
                $this->validateMethodBy($constraint[$key]);
            }
            else
            {
                if(!is_array($constraint[$key]))
                {
                    throw new \Exception("coming_from_state must be an array");
                }
                foreach($constraint[$key] as $state)
                {
                    $this->checkKeyExist($state, $this->entity->getStates(), "$state does not exist");
                }
            }
        }
    }

    private function validatePropertiesConstraint($constraint)
    {
        var_dump($constraint);die;
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