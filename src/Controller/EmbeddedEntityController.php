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
class EmbeddedEntityController extends BaseController
{
    /**
     * @Route(
     *     "entity/{uuid}/embedded/{property_name}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="post_embedded_entity",
     *      methods={"POST"})
     */
    public function postEmbeddedEntityAction(string $uuid, string $property_name, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $user = empty($this->getUser()->getUuid()) ? null : $this->getUser();
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

        $alreadyDefined = in_array($property_name, $properties);
        $properties[$property_name] =  [$alreadyDefined === false ? "0" : count($properties[$property_name]) => $form->getData()];

        $entity->setProperties($properties);

        $conflict_errors = $this->rulesManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);
        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());
        if($entity->getValidationState() !== "__default")
        {
            $this->em->flush();
        }

        if(is_array($state))
        {
            return $this->response->conflict($state, $entity, ['entity_id', 'entity_property', 'entity_basic']);
        }

        $this->handleEvents($request->getMethod(), $entityStates[$entity->getValidationState()], $entity, $eventDispatcher);

        $this->em->flush();

        return $this->response->created($entity, ['entity_complete', 'user_basic']);
    }
}