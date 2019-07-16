<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Entity\Entity;
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
     *     "entity/{entity_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entity_list",
     *      methods={"GET"})
     */
    public function getEntityListAction(string $entity_name, Request $request)
    {

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
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);

        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }
        $state = $entity->getValidationState();
        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$state]['methods'];
        if(!array_key_exists($request->getMethod(),$entityConfig))
        {
            return $this->response->methodNotAllowed($request->getMethod()." is not allowed on this route");
        }

        $isAllowed = $this->isAllowed($entityConfig[$request->getMethod()]['by'], false, $entity);

        if(is_object($isAllowed))
        {
            return $isAllowed;
        }

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $this->handleEvents($request->getMethod(), $entityConfig[$request->getMethod()], $entity, $eventDispatcher);

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

        $formClass = "App\Form\\".$entity_name."\__default\POST";
        if(!class_exists($formClass))
        {
            return $this->response->notFound("Route not found");
        }
        $isAllowed = $this->isAllowed($entity['states']['__default']['methods']['POST']['by']);
        if(is_object($isAllowed))
        {
            return $isAllowed;
        }

        $formObject = new \ReflectionClass($formClass);
        $form = $this->createForm($formObject->getName(), null);
        $form->submit(json_decode($request->getContent(), true), false);

        if(!$form->isValid())
        {
            return $this->response->badRequest('Form invalid');
        }

        $entityToPost = new Entity();
        $entityToPost->setKind($entity_name);
        $entityToPost->setOwner($this->getUser());
        $entityToPost->setValidationState("__default");
        $entityToPost->setProperties($form->getData());

        $conflict_errors = $this->rulesManager->decideConflict($entityToPost , $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $state = $this->validate->processValidation($entityToPost,$entityToPost->getValidationState(), $entity['states'], $this->getUser());
        $this->em->persist($entityToPost);

        if(!is_string($state) || $state === "__default")
        {
            $this->em->flush();
            return $this->response->conflict($state['errors'], $entityToPost, ['entity_id', 'entity_property', "entity_basic"]);
        }

        $entityToPost->setValidationState($state);
        $this->handleEvents($request->getMethod(), $entity['states']['__default'], $entityToPost, $eventDispatcher);

        $this->em->flush();

        return $this->response->created($entityToPost, ['entity_complete', 'user_basic']);
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
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);

        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }
        $state = $entity->getValidationState();
        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$state]['methods'];
        if(!array_key_exists($request->getMethod(),$entityConfig))
        {
            return $this->response->methodNotAllowed($request->getMethod()." is not allowed on this route");
        }

        $isAllowed = $this->isAllowed($entityConfig[$request->getMethod()]['by'], false, $entity);

        if(is_object($isAllowed))
        {
            return $isAllowed;
        }

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $this->handleEvents($request->getMethod(), $entityConfig[$request->getMethod()], $entity, $eventDispatcher);

        $this->em->remove($entity);
        $this->em->flush();

        return $this->response->empty();
    }
}
