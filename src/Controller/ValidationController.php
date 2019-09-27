<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Form\ValidateStateManually;
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
     *     "entities/{uuid}/validation_state",
     *      requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="validate_entity",
     *      methods={"PATCH"})
     */
    public function patchValidationStateManualAction(string $uuid, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($entity)) return $this->response->notFound('Route not found');

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();

        if(empty($user)) return $this->response->notAuthorized();

        $form = $this->createForm(ValidateStateManually::class);
        $form->submit(json_decode($request->getContent(), true), true);

        if(!$form->isValid())
        {
            return $this->response->badForm($form);
        }

        $data = $form->getData();
        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        if(!array_key_exists($data['validation_state'], $entityStates))
        {
            return $this->response->badRequest('');
        }

        $valid = $this->manualValidation->ableToValidateEntity($entity->getValidationState(), $data['validation_state'],$entityStates, $entity, $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $entity->setValidationState($data['validation_state']);
        $this->handleEvents($request->getMethod(), $entityStates[$entity->getValidationState()], $entity, $eventDispatcher);
        $entity->setLastUpdate(new \DateTime('now'));

        $this->dm->flush();
        return $this->response->ok($entity, ['entity_complete', 'user_basic']);
    }

}