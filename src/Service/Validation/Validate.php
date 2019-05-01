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