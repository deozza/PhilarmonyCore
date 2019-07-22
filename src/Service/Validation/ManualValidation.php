<?php


namespace Deozza\PhilarmonyCoreBundle\Service\Validation;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeAccessToEntity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;


class ManualValidation
{
    public function __construct(ResponseMaker $responseMaker, DatabaseSchemaLoader $schemaLoader, AuthorizeAccessToEntity $authorizeAccessToEntity)
    {
        $this->response = $responseMaker;
        $this->schemaLoader = $schemaLoader;
        $this->authorizeAccessToEntity = $authorizeAccessToEntity;
    }

    public function ableToValidateEntity(?Entity $entity, $user)
    {
        $states = $this->setup($entity, $user);
        if(is_object($states))
        {
            return $states;
        }

        $availableStates = array_keys($states);

        $nextStep = $this->moveToStep($availableStates, $entity);

        if(is_object($nextStep))
        {
            return $nextStep;
        }

        $by = $this->checkConstraintExists($states[$nextStep['step']]);
        if(is_object($by))
        {
            return $by;
        }

        $access = $this->authorizeAccessToEntity->authorize($user, $by, $entity);
        if($access === true)
        {
            return $nextStep;
        }
        return $this->response->forbiddenAccess("Access to this resource is forbidden.");
    }

    public function ableToRetrogradeEntity(?Entity $entity, $user)
    {
        $states = $this->setup($entity, $user);
        if(is_object($states))
        {
            return $states;
        }

        $currentState = $states[$entity->getValidationState()];
        $availableStates = array_keys($states);

        $previousStep = $this->moveToStep($availableStates, $entity, false);

        if(is_object($currentState))
        {
            return $currentState;
        }
        $by = $this->checkConstraintExists($currentState);
        if(is_object($by))
        {
            return $by;
        }
        $access = $this->authorizeAccessToEntity->authorize($user, $by, $entity);

        if($access === true)
        {
            return $previousStep;
        }

        return $this->response->forbiddenAccess("Access to this resource is forbidden.");
    }

    private function setup(?Entity $entity, $user)
    {
        if(empty($user))
        {
            return $this->response->notAuthorized();
        }

        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }

        return $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];
    }

    private function checkConstraintExists($state)
    {
        if(!array_key_exists('constraints', $state))
        {
            return $this->response->notFound("Resource not found");
        }


        if(!array_key_exists('manual', $state['constraints']))
        {
            return $this->response->notFound("Resource not found");
        }

        return $state['constraints']['manual']['by'];
    }

    private function moveToStep(array $availableStates, Entity $entity, bool $sup = true)
    {
        foreach($availableStates as $key=>$state)
        {
            if($state === $entity->getValidationState())
            {
                $key = $sup === true ? $key + 1 : $key - 1;
                if(!array_key_exists($key, $availableStates))
                {
                    return $this->response->notFound("Resource not found");
                }
                $step = $availableStates[$key];
            }
        }

        return ['step'=>$step, 'key'=>$key];
    }

}