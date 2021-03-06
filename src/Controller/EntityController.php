<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Entity controller.
 *
 * @Route("api/")
 */
class EntityController extends BaseController
{

    /**
     * @Route(
     *     "entities/{entity_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entity_list",
     *      methods={"GET"})
     */
    public function getEntityListAction(string $entity_name, Request $request)
    {
        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity_name);

        if (empty($entityConfig)) {
            return $this->response->notFound("Resource not found");
        }

        $user = !empty($this->getUser()->getUuidAsString()) ? $this->getUser() : null;

        $filter = $request->query->get("filterBy", []);
        $sort = $request->query->get("sortBy", []);

        $entities = $this->dm->getRepository(Entity::class)->findAllFiltered($filter, $sort, $entity_name);

        foreach($entities as $key=>$entity)
        {
            $state_name = $entity->getValidationState();
            $state_config = $entityConfig['states'][$state_name]['methods'];

            if(!array_key_exists($request->getMethod(), $state_config))
            {
                unset($entities[$key]);
            }

            $access = $this->authorizeAccessToEntity->authorize($user, $state_config[$request->getMethod()]['by'], $entity);

            if($access === false)
            {
                unset($entities[$key]);
            }
        }

        $page = $request->query->getInt("page", 1);
        $count = $request->query->getInt("count", 10);

        $offset = ($page - 1) * $count;
        $total = count($entities);
        $paginatedEntities = array_splice($entities, $offset, $count);

        return $this->response->okPaginated($paginatedEntities, ['entity_basic', 'entity_id','user_basic'], $count, $page, $total);
    }

    /**
     * @Route(
     *     "entity/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
     *     },
     *     name="get_entity",
     *      methods={"GET"})
     */
    public function getEntityAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneByUuid($uuid);
        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $stateConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$entity->getValidationState()];

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher);
        return $this->response->ok($entity, ['entity_basic', 'user_basic']);
    }

    /**
     * @Route(
     *     "entity/{entity_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="post_entity",
     *      methods={"POST"})
     */
    public function postEntityAction(string $entity_name, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);

        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $formClass = $this->formGenerator->getFormNamespace()."$entity_name\__default\POST";

        if(!class_exists($formClass))
        {
            return $this->response->notFound("Route not found");
        }

        $entityToPost = new Entity();
        $entityToPost->setKind($entity_name);
        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $isAllowed = $this->authorizeRequest->isAllowed($entity['states']['__default']['methods']['POST']['by'], $user, true, $entityToPost);
        if(is_object($isAllowed))
        {
            return $isAllowed;
        }

        $formObject = new \ReflectionClass($formClass);
        $form = $this->createForm($formObject->getName(), null);
        $form->submit(json_decode($request->getContent(), true), true);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $owner = [
            'username'=>$user->getUsername(),
            'uuid' => $user->getUuidAsString()
        ];

        $entityToPost->setOwner($owner);
        $entityToPost->setValidationState("__default");
        $data = $form->getData();
        foreach($data as $property => $content)
        {
            if(is_a($content,Entity::class))
            {
                $data[$property] = [
                    "uuid"=>$content->getUuidAsString(),
                    "validationState"=>$content->getValidationState(),
                    "owner"=>[
                        "uuid"=>$content->getOwner()['uuid'],
                        "username"=>$content->getOwner()['username']
                    ],
                    "properties"=>$content->getProperties()
                ];
            }
        }

        $entityToPost->setProperties($data);

        $conflict_errors = $this->rulesManager->decideConflict($entityToPost , $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $this->dm->persist($entityToPost);
        $state = $this->validate->processValidation($entityToPost,0, $entity['states'], $this->getUser());
        if($entityToPost->getValidationState() !== "__default")
        {
            $this->dm->flush();
        }

        if(is_array($state))
        {
            return $this->response->conflict($state, $entityToPost, ['entity_basic', 'entity_id', 'user_basic']);
        }

        $this->handleEvents($request->getMethod(), $entity['states']['__default'], $entityToPost, $eventDispatcher, json_decode($request->getContent(), true));

        $this->dm->flush();

        return $this->response->created($entityToPost, ['entity_basic', 'entity_id', 'user_basic']);
    }

    /**
     * @Route(
     *     "entity/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
     *     },
     *     name="patch_entity",
     *      methods={"PATCH"})
     */
    public function patchEntityAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneByUuid($uuid);

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $stateConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        $formClass = "App\Form\\".$entity->getKind()."\\".$entity->getValidationState()."\PATCH";
        $formObject = new \ReflectionClass($formClass);

        $properties = $entity->getProperties();

        $form = $this->createForm($formObject->getName(), $properties);
        $form->submit(json_decode($request->getContent(), true), false);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $data = $form->getData();

        foreach($data as $property => $content)
        {
            if(is_a($content,Entity::class))
            {
                $data[$property] = [
                    "uuid"=>$content->getUuidAsString(),
                    "validationState"=>$content->getValidationState(),
                    "owner"=>[
                        "uuid"=>$content->getOwner()['uuid'],
                        "username"=>$content->getOwner()['username']
                    ],
                    "properties"=>$content->getProperties()
                ];
            }
        }

        $entity->setProperties($data);

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $state = $this->validate->processValidation($entity,0, $stateConfig, $this->getUser());
        $this->dm->flush();

        if(is_array($state))
        {
            return $this->response->conflict($state, $entity, ['entity_complete', 'user_basic']);
        }

        $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher, json_decode($request->getContent(), true));

        $this->dm->flush();

        return $this->response->ok($entity, ['entity_complete', 'user_basic']);

    }

    /**
     * @Route(
     *     "entity/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
     *     },
     *     name="delete_entity",
     *      methods={"DELETE"})
     */
    public function deleteEntityAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneByUuid($uuid);
        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$entity->getValidationState()];

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $this->handleEvents($request->getMethod(), $entityConfig, $entity, $eventDispatcher);

        $this->dm->remove($entity);
        $this->dm->flush();

        return $this->response->empty();
    }
}