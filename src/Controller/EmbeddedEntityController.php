<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Document\Property;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Entity controller.
 *
 * @Route("api/")
 */
class EmbeddedEntityController extends BaseController
{
    /**
     * @Route(
     *     "entities/{uuid}/embedded/{property_name}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="get_embedded_entity",
     *      methods={"GET"})
     */
    public function getEmbeddedEntityAction(string $uuid, string $property_name, Request $request)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();

        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        return $this->response->ok($entity->getPropertiesByKind($property_name), ['entity_basic', 'entity_id', 'user_basic']);
    }

    /**
     * @Route(
     *     "entities/{uuid}/embedded/{property_name}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="post_embedded_entity",
     *      methods={"POST"})
     */
    public function postEmbeddedEntityAction(string $uuid, string $property_name, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        $formClass = $this->formGenerator->getFormNamespace().$entity->getKind()."\\".$entity->getValidationState()."\\".$property_name."\\".$request->getMethod();

        if(!class_exists($formClass))
        {
            return $this->response->notFound("Route not found");
        }

        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $propertyConfig = $this->schemaLoader->loadPropertyEnumeration($property_name);
        $ableToPostMultiple = array_key_exists('array', $propertyConfig) && $propertyConfig['array'] === true;
        $alreadyPosted = count($entity->getPropertiesByKind($property_name));
        if(!$ableToPostMultiple && $alreadyPosted >=1)
        {
            return $this->response->badRequest(
                    [
                        $property_name => [
                            'This property has already been posted and cannot be posted again.'
                        ]
                    ]
            );
        }

        $formObject = new \ReflectionClass($formClass);
        $form = $this->createForm($formObject->getName(), null);
        $form->submit(json_decode($request->getContent(), true), true);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $property = new Property($property_name, $entity);
        $property->setOwner(['uuid'=>$user->getUuidAsString(), 'username'=>$user->getUsername()]);
        $property->setProperties($form->getData());
        $this->dm->persist($property);

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $embeddedValidation = $this->validate->processEmbeddedValidation($entity, $this->schemaLoader->loadEntityEnumeration($property_name), $this->getUser());
        if(is_array($embeddedValidation))
        {
            $entity->setLastUpdate(new \DateTime('now'));
            $this->dm->flush();
            return $this->response->created(['warning'=>$embeddedValidation, 'entity'=>$entity], ['entity_basic', 'entity_id', 'entity_property']);
        }

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());
        if(is_array($state))
        {
            $entity->setLastUpdate(new \DateTime('now'));
            $this->dm->flush();
            return $this->response->created(['warning'=>$state, 'entity'=>$entity], ['entity_basic', 'entity_id', 'user_basic']);
        }

        $this->handleEvents($request->getMethod(), $entityStates[$entity->getValidationState()], $entity, $eventDispatcher, json_decode($request->getContent(), true));

        $this->dm->flush();

        return $this->response->created($property->getEntity(), ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entities/embedded/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="patch_embedded_entity",
     *      methods={"PATCH"})
     */
    public function patchEmbeddedEntityAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $property = $this->dm->getRepository(Property::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($property))
        {
            return $this->response->notFound("Resource not found");
        }

        $entityStates = $this->schemaLoader->loadEntityEnumeration($property->getEntity()->getKind())['states'];

        $formClass = $this->formGenerator->getFormNamespace().$property->getEntity()->getKind()."\\".$property->getEntity()->getValidationState()."\\".$property->getKind()."\\".$request->getMethod();

        if(!class_exists($formClass))
        {
            return $this->response->notFound("Route not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $valid = $this->authorizeRequest->validateRequest($property->getEntity(), $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $formObject = new \ReflectionClass($formClass);
        $form = $this->createForm($formObject->getName(), $property->getProperties());
        $form->submit(json_decode($request->getContent(), true), false);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $property->setProperties($form->getData());

        $conflict_errors = $this->rulesManager->decideConflict($property->getEntity(), $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $embeddedValidation = $this->validate->processEmbeddedValidation($property->getEntity(), $this->schemaLoader->loadEntityEnumeration($property->getKind()), $this->getUser());
        if(is_array($embeddedValidation))
        {
            $property->getEntity()->setLastUpdate(new \DateTime('now'));
            $this->dm->flush();
            return $this->response->ok(['warning'=>$embeddedValidation, 'entity'=>$property->getEntity()], ['entity_basic', 'entity_id', 'entity_property']);
        }

        $state = $this->validate->processValidation($property->getEntity(),0, $entityStates, $this->getUser());
        if(is_array($state))
        {
            $property->getEntity()->setLastUpdate(new \DateTime('now'));
            $this->dm->flush();
            return $this->response->ok(['warning'=>$state, 'entity'=>$property->getEntity()], ['entity_basic', 'entity_id', 'user_basic']);
        }

        $this->handleEvents($request->getMethod(), $entityStates[$property->getEntity()->getValidationState()], $property->getEntity(), $eventDispatcher, json_decode($request->getContent(), true));

        $this->dm->flush();

        return $this->response->ok($property->getEntity(), ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entities/embedded/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
     *     },
     *     name="delete_embedded_entity",
     *      methods={"DELETE"})
     */
    public function deleteEmbeddedEntityAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $property = $this->dm->getRepository(Property::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($property))
        {
            return $this->response->notFound("Resource not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $valid = $this->authorizeRequest->validateRequest($property->getEntity(), $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $propertyConfig = $this->schemaLoader->loadPropertyEnumeraion($property->getKind());
        $count = count($property->getEntity()->getPropertiesByKind($property->getKind()));
        if($propertyConfig['constraints']['required'] === true && $count <=1)
        {
            return $this->response->badRequest(
                [
                    $property->getKind() => [
                        'This property is required and cannot be deleted.'
                    ]
                ]
            );
        }

        $entityStates = $this->schemaLoader->loadEntityEnumeration($property->getEntity()->getKind())['states'];
        $this->handleEvents($request->getMethod(), $entityStates['__default'], $property->getEntity(), $eventDispatcher);
        $property->getEntity()->setLastUpdate(new \DateTime('now'));

        $this->dm->flush();

        return $this->response->empty();
    }
}