<?php

namespace Deozza\PhilarmonyCoreBundle\Service\Validation;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
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

    public function ableToValidateEntity(string $currentState, string $newState, array $entityStates, Entity $entity, $user)
    {
        $isRetrograding = false;
        try
        {
            $by = $entityStates[$newState]['constraints']['manual']['by'];
            $comingFromStates = $entityStates[$newState]['constraints']['manual']['coming_from_states'];
        }
        catch(\Exception $e)
        {
            $comingFromStates = $entityStates[$currentState]['constraints']['manual']['coming_from_states'];
            $by = $entityStates[$currentState]['constraints']['manual']['by'];
            $isRetrograding = true;
        }
        $ableToValidate = false;
        if(array_key_exists('roles', $by))
        {
            foreach($by['roles'] as $role)
            {
                if(in_array($role, $user->getRoles())) $ableToValidate = true;
            }
        }

        if(array_key_exists('users', $by))
        {
            if(in_array('owner', $by['users']))
            {
                $ableToValidate = $entity->getOwner()['uuid'] === $user->getUuidAsString();
            }
        }

        if(!$ableToValidate) return $this->response->forbiddenAccess('You are not allowed to change the state of this entity');

        if(!$isRetrograding && !in_array($currentState, $comingFromStates))
        {
            return $this->response->badRequest('You can not move to this state');
        }

        if($isRetrograding && !in_array($newState, $comingFromStates))
        {
            return $this->response->badRequest('You can not move to this state');
        }

        return true;
    }
}