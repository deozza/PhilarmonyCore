<?php

namespace Deozza\PhilarmonyCoreBundle\Service\Authorization;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;

class AuthorizeRequest
{
    public function __construct(ResponseMaker $responseMaker, DatabaseSchemaLoader $schemaLoader, AuthorizeAccessToEntity $authorizeAccessToEntity)
    {
        $this->response = $responseMaker;
        $this->schemaLoader = $schemaLoader;
        $this->authorizeAccessToEntity = $authorizeAccessToEntity;
    }
    
    public function validateRequest(?Entity $entity, string $method, $user)
    {
        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }

        $stateConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$entity->getValidationState()]['methods'];

        if(!array_key_exists($method,$stateConfig))
        {
            return $this->response->methodNotAllowed($method);
        }

        $loggedin = false;
        if($method !== "GET")
        {
            $loggedin = true;
        }

        $isAllowed = $this->isAllowed($stateConfig[$method]['by'],$user, $loggedin, $entity);
        if(is_object($isAllowed))
        {
            return $isAllowed;
        }
    }

    public function isAllowed($by, $user, $mustBeLoggedIn = true, ?Entity $entity = null)
    {
        if($mustBeLoggedIn === true && empty($user))
        {
            return $this->response->notAuthorized();
        }

        $access = $this->authorizeAccessToEntity->authorize($user, $by, $entity);

        if($access === true)
        {
            return $access;
        }

        return $this->response->forbiddenAccess("Access to this resource is forbidden.");
    }
}