<?php


namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity;

class ValidateEntity
{
    private $entity;

    public function __construct(EntitySchema $entity, array $propertiesSchema, array $authorizedKeys, array $entitiesSchma)
    {
        $this->entity = $entity;
        $this->propertiesSchema = $propertiesSchema;
        $this->entitiesSchema = $entitiesSchma;
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
            $this->checkKeyExist($property, $this->propertiesSchema, "Property $property does not exist.\nDeclared in ".$this->entity->getEntityName());
        }
    }

    public function validateStates()
    {
        if(empty($this->entity->getStates())) return ;

        $this->checkKeyExist($this->authorizedKeys['default_state'], $this->entity->getStates(),'__default state missing in '.$this->entity->getEntityName());

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
                            throw new \Exception("Constraints must be of type array.\n Declared in the state '$stateName' of the entity '".$this->entity->getEntityName()."'");
                        }
                        $this->validateConstraints($stateKeyData);
                    }break;
                    default: throw new \Exception("$stateKey is not a valid state key.\n Declared in the state '$stateName' of the entity '".$this->entity->getEntityName()."'");break;
                }
            }
            if(!$containsMethod)
            {
                throw new \Exception("A state must contain at least one method.\nDeclared in the state '$stateName' of the entity '".$this->entity->getEntityName()."'");
            }
        }
    }

    public function validateConstraints(array $constraints)
    {
        if(count($constraints)>1)
        {
            throw new \Exception($this->entity->getEntityName()." contains badly formated constraints. ");
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
            default: throw new \Exception("A constraint is applied to ".json_encode($this->authorizedKeys['entity_constraint_keys']).". ".$this->entity->getEntityName()." constraints are invalid.");break;
        }
    }

    private function validateMethod(string $methodName, array $methodData)
    {
        $this->checkArrayContains($methodName, $this->authorizedKeys['methods'], "$methodName method does not exist.\nDeclared in the entity '".$this->entity->getEntityName()."'.");

        if($methodName === 'POST' || $methodName === 'PATCH')
        {
            $this->checkKeyExist($this->authorizedKeys['method_keys'][0], $methodData, "Properties expected in method $methodName. It was not found one state of the entity '".$this->entity->getEntityName()."'.");
            $this->validateFormProperties($methodData[$this->authorizedKeys['method_keys'][0]]);
        }

        $this->checkKeyExist($this->authorizedKeys['method_keys'][1], $methodData, "You must define who is able to execute a method. 'by' key was not declared for the method $methodName of the entity '".$this->entity->getEntityName()."'.");
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
            throw new \Exception("'by' must be an array. It was not declared as such in the entity '".$this->entity->getEntityName()."'.");
        }
        foreach($by as $byKind=>$byData)
        {
            $this->checkArrayContains($byKind, $this->authorizedKeys['by_keys'], "'by' expected to be ".json_encode($this->authorizedKeys['by_keys']).". Invalid value '$byKind' found in '".$this->entity->getEntityName()."'.");
        }
    }

    private function validateManualConstraint($constraint)
    {
        if(count($constraint) != 2)
        {
            throw new \Exception("Badly formatted manual constraint found in the entity '".$this->entity->getEntityName()."'.");
        }

        foreach($this->authorizedKeys['manual_constraint_keys'] as $key)
        {
            $this->checkKeyExist($key, $constraint, "A manual constraint must contain $key. It was not found in the entity '".$this->entity->getEntityName()."'.");
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
                    $this->checkKeyExist($state, $this->entity->getStates(), "$state, used in a manual constraint, was not found in the entity '".$this->entity->getEntityName()."'.");
                }
            }
        }
    }

    private function validatePropertiesConstraint($constraint)
    {
        preg_match_all('/([^\.])+/', array_key_first($constraint), $matches);
        $propertyKey = 0;
        if(count($matches[0]) > 1)
        {
            if($this->entity->getEntityName() !== $matches[0][0])
            {
                throw new \Exception("Entity ".$matches[0][0]." does not exist from constraint");
            }
            $propertyKey++;

        }

        $this->checkArrayContains($matches[0][$propertyKey], $this->entity->getProperties(), 'Property '.$matches[0][$propertyKey]." does not exist in ".$this->entity->getEntityName());

        if(!is_array($constraint[array_key_first($constraint)]))
        {
            throw new \Exception('Constraint in '.$this->entity->getEntityName()." must be an array");
        }

        foreach($constraint[array_key_first($constraint)] as $data)
        {
            $extractedData = $this->extractDataFromConstraint($data);
            $this->validateOperators($extractedData);
        }
    }

    private function extractDataFromConstraint(string $data)
    {
        $extractedData = [];
        $regex = "/(?<=\()[^\)]*|[.\w]+/";
        preg_match_all($regex, $data, $matches);
        for($i = 0; $i<count($matches[0]); $i+=2)
        {
            $extractedData[$matches[0][$i]] = $matches[0][$i+1];
        }
        return $extractedData;
    }

    private function validateOperators(array $operators)
    {
        $firstOperator = array_key_first($operators);
        $explodedFirstOperator = explode('.', $firstOperator);
        $this->checkArrayContains($explodedFirstOperator[0], $this->authorizedKeys['basic_constraint_operators'], $explodedFirstOperator[0]." is not a valid operator.\n Declared in the entity '".$this->entity->getEntityName()."'.");

        switch($explodedFirstOperator[1])
        {
            case 'self':
            {
                $this->validateSelfPropertiesTarget($operators[$firstOperator], $this->entity->getProperties(), [$this->entity->getEntityName()], $explodedFirstOperator[0]);
            }break;
            case 'value':
            {

            }break;
            default:
            {
                $this->validateExternalPropertiesTarget($explodedFirstOperator[1], $operators[$firstOperator], $this->entitiesSchema, $explodedFirstOperator[0]);
            }break;
        }
    }

    private function validateExternalPropertiesTarget(string $entityTarget, string $propertiesTarget, array $entityRange, string $operator)
    {
        $this->checkKeyExist($entityTarget, $entityRange, "Entity '$entityTarget' does not exist.\n Declared in a constraint of '".$this->entity->getEntityName()."'.");
        $propertyRange = $entityRange[$entityTarget]['properties'];
        $this->validateSelfPropertiesTarget($propertiesTarget, $propertyRange, [$entityTarget], $operator);
    }

    private function validateSelfPropertiesTarget(string $propertiesTarget, array $propertyRange, array $entityRange, string $operator)
    {
        $explodedPropertiesTarget = explode(',', $propertiesTarget);
        if(($operator === 'b' || $operator === 'nb') && count($explodedPropertiesTarget) < 2)
        {
            throw new \Exception("Constraint 'b' and 'nb' must contain 2 values. Not enough values found in a constraint of '".$this->entity->getEntityName()."'.");
        }

        foreach($explodedPropertiesTarget as $target)
        {
            $explodedTarget = explode('.',$target);
            $propertyId = 0;
            if(count($explodedTarget) === 2)
            {
                $propertyId = 1;
                if(!in_array($explodedTarget[0], $entityRange))
                {
                    throw new \Exception("Entity target ".$explodedTarget[0]." does not exist. \n Declared in a constraint of '".$this->entity->getEntityName()."'.");
                }
            }
            $this->checkArrayContains($explodedTarget[$propertyId],$propertyRange, 'Property '.$explodedTarget[$propertyId].' does not exist in '.json_encode($entityRange).". \n Declared in a constraint of '".$this->entity->getEntityName()."'.");
        }
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