<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\FormManager\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyBundle\Service\Validation\Validate;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Entity controller.
 *
 * @Route("api/")
 */
class ValidationController extends AbstractController
{

    public function __construct(ResponseMaker $responseMaker,
                                EntityManagerInterface $em,
                                PaginatorInterface $paginator,
                                ProcessForm $processForm,
                                DatabaseSchemaLoader $schemaLoader,
                                RulesManager $ruleManager,
                                Validate $validate)
    {
        $this->response = $responseMaker;
        $this->em = $em;
        $this->processForm = $processForm;
        $this->schemaLoader = $schemaLoader;
        $this->ruleManager = $ruleManager;
        $this->paginator = $paginator;
        $this->validator = $validate;
    }

    /**
     * @Route(
     *     "validate/{id}",
     *      requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="validate_entity",
     *      methods={"POST"})
     */
    public function postManualValidationAction($id, Request $request)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($entity))
        {
            return $this->response->notFound("Entity with the id $id was not found");
        }

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind());

        $state = $this->validator->processValidation($entity,$entity->getValidationState(), $entityConfig['states'], $this->getUser(), null, true);
        if(is_array($state))
        {
            return $this->response->conflict($state['errors'],$entity, ['entity_complete', 'user_basic']);
        }

        $this->em->flush();

        return $this->response->ok($entity, ['entity_complete', 'user_basic']);

    }
}
