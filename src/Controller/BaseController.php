<?php

namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeAccessToEntity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeRequest;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyCoreBundle\Service\FormManager\FormGenerator;
use Deozza\PhilarmonyCoreBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyCoreBundle\Service\Validation\ManualValidation;
use Deozza\PhilarmonyCoreBundle\Service\Validation\Validate;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BaseController extends AbstractController
{
    public function __construct(
        DatabaseSchemaLoader $schemaLoader,
        FormGenerator $formGenerator,
        ResponseMaker $responseMaker,
        Validate $validate,
        ManualValidation $manualValidation,
        AuthorizeAccessToEntity $authorizeAccessToEntity,
        AuthorizeRequest $authorizeRequest,
        RulesManager $rulesManager,
        DocumentManager $dm
    )
    {
        $this->schemaLoader = $schemaLoader;
        $this->formGenerator = $formGenerator;
        $this->response = $responseMaker;
        $this->validate = $validate;
        $this->manualValidation = $manualValidation;
        $this->authorizeAccessToEntity = $authorizeAccessToEntity;
        $this->authorizeRequest = $authorizeRequest;
        $this->rulesManager = $rulesManager;
        $this->dm = $dm;
    }

    protected function handleEvents(string $method, array $stateConfig, Entity $entity, EventDispatcherInterface $eventDispatcher, array $payload = null)
    {
        if(!array_key_exists($method,$stateConfig['methods'])) {
            return;
        }
        if(!array_key_exists('post_scripts',$stateConfig['methods'][$method])) {
            return;
        }
        $scripts = $stateConfig['methods'][$method]['post_scripts'];

        $event = new GenericEvent(['entity'=>$entity, 'payload'=>$payload]);
        foreach($scripts as $script)
        {
            $eventDispatcher->dispatch($script, $event);
        };
    }
}