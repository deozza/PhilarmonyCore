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
class EmbeddedEntityController extends BaseController
{
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

        $formObject = new \ReflectionClass($formClass);
        $form = $this->createForm($formObject->getName(), null);
        $form->submit(json_decode($request->getContent(), true), true);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $properties = $entity->getProperties();
        $data = $form->getData();
        foreach($data as $key=>$value)
        {
            if(is_a($value, Entity::class))
            {
                $data[$key] = [
                    "uuid"=>$value->getUuidAsString(),
                    "validationState"=>$value->getValidationState(),
                    "owner"=>[
                        "uuid"=>$value->getOwner()['uuid'],
                        "username"=>$value->getOwner()['username'],
                    ],
                    "properties"=>$value->getProperties()
                ];
            }
        }

        $alreadyDefined = array_key_exists($property_name, $properties);

        $propertyConfig = $this->schemaLoader->loadPropertyEnumeration($property_name);
        $isArray = array_key_exists('array', $propertyConfig) && $propertyConfig['array'] === true;

        if($isArray === true)
        {
            if(empty($properties[$property_name]))
            {
                $properties[$property_name] = [0 => $data];
            }
            else
            {
                array_push($properties[$property_name], $data);
            }
        }
        else
        {
            if($alreadyDefined === true)
            {
                return $this->response->badRequest("'$property_name' already exists in '$entity'. Can not be added.");
            }

            $properties[$property_name] = $data;
        }

        $entity->setProperties($properties);

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $embeddedValidation = $this->validate->processEmbeddedValidation($entity, $this->schemaLoader->loadEntityEnumeration($property_name), $this->getUser());
        if(is_array($embeddedValidation))
        {
            return $this->response->conflict($embeddedValidation, $entity, ['entity_id', 'entity_property', 'entity_basic']);
        }

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());
        if(is_array($state))
        {
            $this->dm->flush();
            return $this->response->created(['warning'=>$state, 'entity'=>$entity], ['entity_basic', 'entity_id', 'user_basic']);
        }

        $this->handleEvents($request->getMethod(), $entityStates[$entity->getValidationState()], $entity, $eventDispatcher, json_decode($request->getContent(), true));
        $entity->setLastUpdate(new \DateTime('now'));

        $this->dm->flush();

        return $this->response->created($entity, ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entities/{uuid}/embedded/{property_name}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="patch_embedded_entity",
     *      methods={"PATCH"})
     */
    public function patchEmbeddedEntityAction(string $uuid, string $property_name, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $properties = $entity->getProperties();
        if(!array_key_exists($property_name, $properties) || empty($properties[$property_name]))
        {
            return $this->response->notFound("Resource not found");
        }
        $property_id = $request->query->get("propertyId", null);

        if(!empty($property_id) &&
            (!array_key_exists($property_id, $properties[$property_name]) || empty($properties[$property_name][$property_id]))
        )
        {
            return $this->response->notFound("Resource not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();

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
        $formObject = new \ReflectionClass($formClass);
        $form = $this->createForm($formObject->getName(), null);
        $form->submit(json_decode($request->getContent(), true), true);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $properties = $entity->getProperties();
        $data = $form->getData();
        foreach($data as $key=>$value)
        {
            if(is_a($value, Entity::class))
            {
                $data[$key] = [
                    "uuid"=>$value->getUuidAsString(),
                    "validationState"=>$value->getValidationState(),
                    "owner"=>[
                        "uuid"=>$value->getOwner()->getUuidAsString(),
                        "username"=>$value->getOwner()->getUsername(),
                    ],
                    "properties"=>$value->getProperties()
                ];
            }
        }
        if(!empty($property_id))
        {
            $properties[$property_name][$property_id] = $data;
        }
        else
        {
            $properties[$property_name] = $data;
        }

        $entity->setProperties($properties);

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $embeddedValidation = $this->validate->processEmbeddedValidation($entity, $this->schemaLoader->loadEntityEnumeration($property_name), $this->getUser());
        if(is_array($embeddedValidation))
        {
            return $this->response->conflict($embeddedValidation, $entity, ['entity_id', 'entity_property', 'entity_basic']);
        }
        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());

        if(is_array($state))
        {
            $this->dm->flush();
            return $this->response->ok(['warning'=>$state, 'entity'=>$entity], ['entity_id', 'entity_property', 'entity_basic']);
        }

        $this->handleEvents($request->getMethod(), $entityStates[$entity->getValidationState()], $entity, $eventDispatcher, json_decode($request->getContent(), true));
        $entity->setLastUpdate(new \DateTime('now'));

        $this->dm->flush();

        return $this->response->ok($entity, ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entities/{uuid}/embedded/{property_name}/{property_id}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$",
     *          "property_id" = "^(\w{1,50})$"
     *     },
     *     name="delete_embedded_entity",
     *      methods={"DELETE"})
     */
    public function deleteEmbeddedEntityAction(string $uuid, string $property_name, string $property_id, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $properties = $entity->getProperties();
        if(!array_key_exists($property_name, $properties) || empty($properties[$property_name]))
        {
            return $this->response->notFound("Resource not found");
        }

        if(!array_key_exists($property_id, $properties[$property_name]) || empty($properties[$property_name][$property_id]))
        {
            return $this->response->notFound("Resource not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();
        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        unset($properties[$property_name][$property_id]);

        $entity->setProperties($properties);

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());
        if($entity->getValidationState() !== "__default")
        {
            $this->dm->flush();
        }
        if(is_array($state))
        {
            return $this->response->conflict($state, $entity, ['entity_id', 'entity_property', 'entity_basic']);
        }

        $this->handleEvents($request->getMethod(), $entityStates['__default'], $entity, $eventDispatcher);
        $entity->setLastUpdate(new \DateTime('now'));

        $this->dm->flush();

        return $this->response->empty();
    }
}