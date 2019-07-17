<?php

namespace Deozza\PhilarmonyCoreBundle\Controller;


use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyCoreBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyCoreBundle\Service\Validation\Validate;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BaseController extends AbstractController
{
    public function __construct(DatabaseSchemaLoader $schemaLoader, ResponseMaker $responseMaker, Validate $validate, RulesManager $rulesManager, EntityManagerInterface $em)
    {
        $this->schemaLoader = $schemaLoader;
        $this->response = $responseMaker;
        $this->validate = $validate;
        $this->rulesManager = $rulesManager;
        $this->em = $em;
    }

    protected function validateRequest(?Entity $entity, string $method)
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

        $isAllowed = $this->isAllowed($stateConfig[$method]['by'], $loggedin, $entity);
        if(is_object($isAllowed))
        {
            return $isAllowed;
        }
    }

    protected function ableToValidateEntity(?Entity $entity)
    {
        if(empty($this->getUser()->getUuid()))
        {
            return $this->response->notAuthorized();
        }

        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }

        $states = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        $availableStates = array_keys($states);

        foreach($availableStates as $key=>$state)
        {
            if($state === $entity->getValidationState())
            {
                if(!array_key_exists($key + 1, $availableStates))
                {
                    return $this->response->notFound("Resource not found");
                }
                $nextStep = $availableStates[$key + 1];
            }
        }

        if(!array_key_exists('constraints', $states[$nextStep]))
        {
            return $this->response->notFound("Resource not found");
        }


        if(!array_key_exists('manual', $states[$nextStep]['constraints']))
        {
            return $this->response->notFound("Resource not found");
        }

        $by = $states[$nextStep]['constraints']['manual']['by'];

        if(array_key_exists('roles', $by))
        {
            foreach($by['roles'] as $role)
            {
                if(in_array($role, $this->getUser()->getRoles())) return ['state'=>$nextStep, 'key'=>$key];
            }
        }

        if(array_key_exists("users", $by))
        {
            foreach($by['users'] as $userKind)
            {
                $userPath = explode('.', $userKind);
                if($userPath[0] === "owner")
                {
                    if($entity->getOwner()->getId() === $this->getUser()->getId())
                    {
                        return ['state'=>$nextStep, 'key'=>$key];
                    }
                }
                else
                {
                    $properties = $entity->getProperties();
                    for($i = 0; $i < count($userPath); $i++)
                    {
                        $properties = $properties[$userPath[$i]];
                    }

                    if($this->getUser()->getId() === $properties || in_array($this->getUser()->getId(), $properties))
                    {
                        return ['state'=>$nextStep, 'key'=>$key];
                    }
                }
            }
        }
        return $this->response->forbiddenAccess("Access to this resource is forbidden.");

    }

    protected function ableToRetrogradeEntity(?Entity $entity)
    {
        if(empty($this->getUser()->getUuid()))
        {
            return $this->response->notAuthorized();
        }

        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }

        $states = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        $currentState = $states[$entity->getValidationState()];
        $availableStates = array_keys($states);

        foreach($availableStates as $key=>$state)
        {
            if($state === $entity->getValidationState())
            {
                if(!array_key_exists($key - 1, $availableStates))
                {
                    return $this->response->notFound("Resource not found");
                }
                $previousState= $availableStates[$key - 1];
            }
        }

        if(!array_key_exists('constraints', $currentState))
        {
            return $this->response->notFound("Resource not found");
        }

        if(!array_key_exists('manual', $currentState['constraints']))
        {
            return $this->response->notFound("Resource not found");
        }

        $by = $currentState['constraints']['manual']['by'];

        if(array_key_exists('roles', $by))
        {
            foreach($by['roles'] as $role)
            {
                if(in_array($role, $this->getUser()->getRoles())) return ['state'=>$previousState, 'key'=>$key];
            }
        }

        if(array_key_exists("users", $by))
        {
            foreach($by['users'] as $userKind)
            {
                $userPath = explode('.', $userKind);
                if($userPath[0] === "owner")
                {
                    if($entity->getOwner()->getId() === $this->getUser()->getId())
                    {
                        return ['state'=>$previousState, 'key'=>$key];
                    }
                }
                else
                {
                    $properties = $entity->getProperties();
                    for($i = 0; $i < count($userPath); $i++)
                    {
                        $properties = $properties[$userPath[$i]];
                    }

                    if($this->getUser()->getId() === $properties || in_array($this->getUser()->getId(), $properties))
                    {
                        return ['state'=>$previousState, 'key'=>$key];
                    }
                }
            }
        }

        return $this->response->forbiddenAccess("Access to this resource is forbidden.");
    }


    protected function isAllowed($by, $loggedin = true, Entity $entity = null)
    {
        if($loggedin === true && empty($this->getUser()->getUuid()))
        {
            return $this->response->notAuthorized();
        }

        if($by === "all") return true;
        if(array_key_exists('roles', $by))
        {
            foreach($by['roles'] as $role)
            {
                if(in_array($role, $this->getUser()->getRoles())) return true;
            }

        }

        if(array_key_exists("users", $by))
        {
            foreach($by['users'] as $userKind)
            {
                $userPath = explode('.', $userKind);
                if($userPath[0] === "owner")
                {
                    if($entity->getOwner()->getId() === $this->getUser()->getId())
                    {
                        return true;
                    }
                }
                else
                {
                    $properties = $entity->getProperties();
                    for($i = 0; $i < count($userPath); $i++)
                    {
                        $properties = $properties[$userPath[$i]];
                    }

                    if($this->getUser()->getId() === $properties || in_array($this->getUser()->getId(), $properties))
                    {
                        return true;
                    }
                }
            }
        }
        return $this->response->forbiddenAccess("Access to this resource is forbidden.");
    }

    protected function handleEvents(string $method, array $stateConfig, Entity $entity, EventDispatcherInterface $eventDispatcher)
    {
        if(!array_key_exists('post_scripts',$stateConfig)) {
            return;
        }
        $scripts = $stateConfig['post_scripts'];

        $event = new GenericEvent($entity);
        foreach($scripts as $script)
        {
            $eventDispatcher->dispatch($event, $script);
        };
    }
}