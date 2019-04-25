<?php

namespace Deozza\PhilarmonyBundle\Service\Validation;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;

class Validate
{

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
            if(!is_array($isValid))
            {
                if(isset($possibleStates[$currentState +1]))
                {
                    $nextState = $possibleStates[$currentState +1];
                    return $this->processValidation($entity, $nextState, $entityStates, $user, $state, $manual);
                }
                return $lastState;
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
                    $errors[] = [
                        $type=>"The ".$entity->getKind()." needs to be approved to pass to the next state."
                        ];
                }
                else
                {
                    $validUser = false;
                    $validRole = false;
                    if(isset($constraint['by']['roles']))
                    {
                        foreach($constraint['by']['roles'] as $role)
                        {
                            if(in_array($role, $user->getRoles()))
                            {
                                $validRole = true;
                            }
                        }
                    }

                    if(isset($constraint['by']['users']))
                    {

                    }

                    if(!$validRole && !$validUser)
                    {
                        $errors[] = [$type=>"FORBIDDEN"];
                    }
                }
            }
        }

        return $errors;
    }

    private function choseFunction($submited, $functionName)
    {

    }

    private function greaterThanOrEqual($submited, $constraint)
    {


    }

    private function lesserThanOrEqual($submited, $constraint)
    {

    }

    private function greaterThan($submited, $constraint)
    {

    }

    private function lesserThan($submited, $constraint)
    {

    }

    private function equal($submited, $constraint)
    {

    }
}