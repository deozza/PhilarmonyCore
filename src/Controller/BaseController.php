<?php

namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeAccessToEntity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeRequest;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyCoreBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyCoreBundle\Service\Validation\ManualValidation;
use Deozza\PhilarmonyCoreBundle\Service\Validation\Validate;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BaseController extends AbstractController
{
    public function __construct(DatabaseSchemaLoader $schemaLoader, ResponseMaker $responseMaker, Validate $validate, ManualValidation $manualValidation, AuthorizeAccessToEntity $authorizeAccessToEntity, AuthorizeRequest $authorizeRequest, RulesManager $rulesManager, EntityManagerInterface $em)
    {
        $this->schemaLoader = $schemaLoader;
        $this->response = $responseMaker;
        $this->validate = $validate;
        $this->manualValidation = $manualValidation;
        $this->authorizeAccessToEntity = $authorizeAccessToEntity;
        $this->authorizeRequest = $authorizeRequest;
        $this->rulesManager = $rulesManager;
        $this->em = $em;
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