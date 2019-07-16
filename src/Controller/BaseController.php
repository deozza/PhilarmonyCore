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
        if(isset($stateConfig['methods'][$method]['post_scripts']))
        {
            $scripts = $stateConfig['methods'][$method]['post_scripts'];

            $event = new GenericEvent($entity);
            foreach($scripts as $script)
            {
                $eventDispatcher->dispatch($event, $script);
            };
        }
    }
}