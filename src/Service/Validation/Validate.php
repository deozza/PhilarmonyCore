<?php

namespace Deozza\PhilarmonyBundle\Service\Validation;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use function Sodium\compare;
use function Symfony\Component\VarDumper\Tests\Fixtures\bar;

class Validate
{
    const OPERATOR_TABLE = [
        ">" => "<=",
        "<" => ">=",
        ">=" => "<",
        "<=" => ">",
        "!=" => "==",
        "!between" => "not between",
        "between" => "between"

    ];

    public function __construct(ResponseMaker $responseMaker, DatabaseSchemaLoader $schemaLoader, EntityManagerInterface $em)
    {
        $this->response = $responseMaker;
        $this->schemaLoader = $schemaLoader;
        $this->em = $em;
    }

    public function processValidation(Entity $entity,$state, $entityStates, $user, $lastState = null, $manual = false)
    {
        $possibleStates = array_keys($entityStates);
        $currentState = array_search($state, $possibleStates);
        if(!array_key_exists("constraints", $entityStates[$state]))
        {
            if(isset($possibleStates[$currentState +1]))
            {
                $nextState = $possibleStates[$currentState +1];
                return $this->processValidation($entity, $nextState, $entityStates, $user, $state, $manual);
            }
            return $lastState;
        }
        else
        {
            $isValid = $this->validateEntity($entity, $user, $entityStates[$state]['constraints'], $manual);
            if(empty($isValid))
            {
                if(isset($possibleStates[$currentState +1]))
                {
                    $nextState = $possibleStates[$currentState +1];
                    return $this->processValidation($entity, $nextState, $entityStates, $user, $state, $manual);
                }
                $entity->setValidationState($state);

                return $state;
            }
            $entity->setValidationState($lastState);

            return ["state"=>$lastState, "errors"=>$isValid];
        }
    }

    private function validateEntity(Entity $entity, $user, array $constraints, $manual)
    {
        $errors = [];
        foreach($constraints as $type=>$constraint)
        {
            if($type === "manual")
            {
                if($manual !== true)
                {
                    $errors = [
                        $type=>"The ".$entity->getKind()." needs to be approved to pass to the next state."
                    ];
                }
                else
                {
                    $authorized = $this->validateUserPermission($constraint['by'], $user, $entity);

                    if($authorized === false)
                    {
                        $errors= ["FORBIDDEN"=> "Access to this resource is forbidden."];
                    }
                }
            }
            else
            {
                foreach ($constraint as $cons)
                {
                    $constraintFunction = explode('(',$cons);
                    $functionName = explode(".",$constraintFunction[0]);

                    $property = explode(".",$type);

                    if($property[0]=== "properties")
                    {
                        $submited = $entity->getProperties();
                        for($i = 1; $i < count($property); $i++)
                        {
                            if(is_object($submited))
                            {
                                $get = "get".ucfirst($property[$i]);
                                $submited = $submited->$get();
                            }
                            else
                            {
                                $submited = $submited[$property[$i]];
                            }
                        }
                    }
                    $error = $this->choseFunction($submited, $constraintFunction, $entity);
                    if(!empty($error))
                    {
                        $errors[$type] = $error;
                    }
                }
            }
        }

        return $errors;
    }


    public function validateUserPermission($constraint, $user, Entity $entity)
    {
        $isAuthorized = false;


        if(isset($constraint['roles']))
        {
            foreach($constraint['roles'] as $role)
            {
                if(in_array($role, $user->getRoles()))
                {
                    $isAuthorized = true;
                }
            }
        }

        if(isset($constraint['users']))
        {
            foreach($constraint['users'] as $userKind)
            {

                $userPath = explode('.', $userKind);
                if($userPath[0] === "owner")
                {
                    if($entity->getOwner()->getId() === $user->getId())
                    {
                        $isAuthorized = true;
                    }
                }
                else
                {

                }
            }
        }
        return $isAuthorized;
    }

    private function choseFunction($submited, $functionName, $entity)
    {
        $function = explode(".", $functionName[0]);
        $method = $function[0];
        $entityToCompare = null;
        $valueToCompare = $functionName[1];
        if(isset($function[1]) && $function[1] === "self")
        {
            $entityToCompare = $entity;
        }
        if(isset($function[1]) && $function[1] !== "self")
        {
            $entityToCompare = $function[1];
        }

        $operator = "";
        switch($method)
        {
            case "greaterThanOrEqual": $operator = "<" ;break;
            case "lesserThanOrEqual" : $operator = ">" ;break;
            case "greaterThan"       : $operator = "<=";break;
            case "lesserThan"        : $operator = ">=";break;
            case "equal"             : $operator = "!=";break;
            case "notBetween"        : $operator = "!between";break;

        }
        return $this->method($submited, $valueToCompare, $operator, $entityToCompare);
    }

    private function method($submited, $valueToCompare, $operator, $entityToCompare = null)
    {
        $valueToCompare = substr($valueToCompare, 0, strlen($valueToCompare)-1);

        $startOfCompare = substr($valueToCompare, 0, 1);
        if($startOfCompare === "#")
        {
            $valueToCompare = substr($valueToCompare, 1);
        }

        if(!empty($entityToCompare))
        {
            if(!is_a($entityToCompare, Entity::class))
            {
                if(strpos($operator, "between"))
                {
                    $valuesToCompare = explode(',', $valueToCompare);
                    $result = $this->em->getRepository(Entity::class)->findAllBetweenForValidate($entityToCompare, $valuesToCompare[0], $valuesToCompare[1], $submited);
                }
                else
                {
                    $result = $this->em->getRepository(Entity::class)->findAllForValidate($entityToCompare, $valueToCompare, $submited, $operator);
                }

                if(substr($operator, 0, 1) === "!" && count($result) > 0)
                {
                    return "Must be ".self::OPERATOR_TABLE[$operator]." others $valueToCompare";
                }
                elseif (substr($operator, 0, 1) !== "!" && count($result) === 0)
                {
                    return "Must be ".self::OPERATOR_TABLE[$operator]." others $valueToCompare";
                }
            }
            else
            {
                $compareTo = $this->getCompareTo($valueToCompare, $entityToCompare->getProperties());
                if(is_a($submited, \DateTime::class) && is_a($compareTo, \DateTime::class))
                {
                    if($this->compareDate($submited, $operator, $compareTo) === true)
                    {
                        return "Must be ".self::OPERATOR_TABLE[$operator]." to ".$compareTo->format('Y-m-d');
                    }
                }
                else
                {
                    if($this->compareWithEval($submited, $operator, $compareTo) === true)
                    {
                        return "Must be ".self::OPERATOR_TABLE[$operator]." to $compareTo";
                    }
                }
            }
        }
        else
        {
            if($this->compareWithEval($submited, $operator, $valueToCompare) === true)
            {
                return "Must be ".self::OPERATOR_TABLE[$operator]." to $valueToCompare";
            }
        }

        return null;
    }


    private function getCompareTo($valueToCompare, $compareTo)
    {
        $properties = explode(".", $valueToCompare);
        for($i = 0; $i < count($properties); $i++)
        {
            if(is_object($compareTo))
            {
                $get = "get".ucfirst($properties[$i]);
                $compareTo = $compareTo->getProperties()[$properties[$i]];
            }
            else
            {
                $compareTo = $compareTo[$properties[$i]];
            }
        }

        return $compareTo;
    }

    private function compareDate($submited, $operator, $compareTo)
    {
        $submited = $submited->getTimestamp();
        $compareTo = $compareTo->getTimestamp();
        return $this->compareWithEval($submited, $operator, $compareTo);
    }

    private function compareWithEval($submited, $operator, $compareTo)
    {
        eval("\$result =  '$submited' $operator '$compareTo';");
        return $result;
    }
}