<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyCoreBundle\Service\FormManager\ProcessForm;
use Deozza\ResponseMaker\Service\ResponseMaker;
use Deozza\PhilarmonyCoreBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyCoreBundle\Service\Validation\Validate;
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
        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($entity))
        {
            return $this->response->notFound("Entity with the id $id was not found");
        }

        try
        {
            $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind());
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        $state = $this->validator->processValidation($entity,$entity->getValidationState(), $entityConfig['states'], $this->getUser(), null, true);

        if(is_array($state))
        {
            if(array_key_exists("FORBIDDEN",$state['errors']))
            {
                return $this->response->forbiddenAccess($state['errors']['FORBIDDEN']);
            }
            return $this->response->conflict($state['errors'],$entity, ['entity_complete', 'user_basic']);
        }

        $this->em->flush();

        return $this->response->ok($entity, ['entity_complete', 'user_basic']);

    }
}
