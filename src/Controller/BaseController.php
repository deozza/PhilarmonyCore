<?php

namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Entity\Entity as MySQLEntity;
use Deozza\PhilarmonyCoreBundle\Document\Entity as MongoDBEntity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeAccessToEntity;
use Deozza\PhilarmonyCoreBundle\Service\Authorization\AuthorizeRequest;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyCoreBundle\Service\FormManager\FormGenerator;
use Deozza\PhilarmonyCoreBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyCoreBundle\Service\Validation\ManualValidation;
use Deozza\PhilarmonyCoreBundle\Service\Validation\Validate;
use Deozza\ResponseMakerBundle\Service\ResponseMaker;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class BaseController extends AbstractController
{
    public function __construct(
        string $orm,
        DatabaseSchemaLoader $schemaLoader,
        FormGenerator $formGenerator,
        ResponseMaker $responseMaker,
        Validate $validate,
        ManualValidation $manualValidation,
        AuthorizeAccessToEntity $authorizeAccessToEntity,
        AuthorizeRequest $authorizeRequest,
        RulesManager $rulesManager,
        EntityManagerInterface $em,
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

        $this->orm = $orm;
        $this->em = $em;
        $this->entityClassName = MySQLEntity::class;
        if($orm === 'mongodb')
        {
            $this->em = $dm;
            $this->entityClassName = MongoDBEntity::class;
        }
    }

    protected function handleEvents(string $method, array $stateConfig, $entity, EventDispatcherInterface $eventDispatcher, array $payload = null)
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