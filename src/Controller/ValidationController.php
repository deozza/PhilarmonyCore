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
class ValidationController extends BaseController
{
    /**
     * @Route(
     *     "validate/{uuid}",
     *      requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="validate_entity",
     *      methods={"POST"})
     */
    public function postManualValidationAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);
        
        $user = empty($this->getUser()->getUuid()) ? null : $this->getUser();
        
        $valid = $this->manualValidation->ableToValidateEntity($entity, $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $entity->setValidationState($valid['state']);
        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];
        $state = $this->validate->processValidation($entity,$valid['key']+1, $entityStates, $this->getUser());
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
        return $this->response->ok($entity, ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "retrograde/{uuid}",
     *      requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="retrograde_entity",
     *      methods={"POST"})
     */
    public function postManualRetrogradeAction(string $uuid, Request $request)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);
        $user = empty($this->getUser()->getUuid()) ? null : $this->getUser();

        $valid = $this->manualValidation->ableToRetrogradeEntity($entity, $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $entity->setValidationState($valid['state']);
        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        $state = $this->validate->processValidation($entity,$valid['key'], $entityStates, $this->getUser(), $valid['key'] - 1);
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
        return $this->response->ok($entity, ['entity_complete', 'user_basic']);

    }
}
