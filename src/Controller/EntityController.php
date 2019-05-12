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
class EntityController extends AbstractController
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
     *     "entity/{entity_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entity_list",
     *      methods={"GET"})
     */
    public function getEntityListAction($entity_name, Request $request)
    {
        $exists= $this->schemaLoader->loadEntityEnumeration($entity_name);

        if(empty($exists))
        {
            return $this->response->notFound("This route does not exists%s");
        }

        $access_errors = $this->ruleManager->decideAccess($exists, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not access to the $entity_name");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exists, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to the the $entity_name", $conflict_errors);
        }


        $propertyFilter = $request->query->get("property", []);
        $entityFilter = $request->query->get("entity", []);
        try
        {
            $entitiesQuery = $this->em->getRepository(Entity::class)->findAllFiltered($propertyFilter, $entityFilter, $entity_name);

            foreach($entitiesQuery as $key=>$item)
            {
                $constraints = $exists['states'][$item->getValidationState()]['methods'][$request->getMethod()]['by'];
                if($constraints !== "all")
                {
                    if(empty($this->getUser()->getUsername()))
                    {
                        unset($entitiesQuery[$key]);
                        continue;
                    }

                    $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $item);

                    if($isAuthorized === false)
                    {
                        unset($entitiesQuery[$key]);
                        continue;
                    }
                }
            }

            $entities = $this->paginator->paginate(
                $entitiesQuery,
                $request->query->getInt("page", 1),
                $request->query->getInt("number", 10)
            );

        }
        catch(\Exception $e)
        {
            return $this->response->notFound("No entity was found with these filters");
        }

        return $this->response->okPaginated($entities, ['entity_complete']);
    }

    /**
     * @Route(
     *     "entity/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="get_entity",
     *     methods={"GET"})
     */
    public function getEntityAction($id, Request $request)
    {
        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The entity with the id $id does not exist");
        }

        $state = $exist->getValidationState();

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($exist->getKind());

        if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $constraints = $entityConfig['states'][$state]['methods'][$request->getMethod()]['by'];

        if($constraints === "all")
        {
            return $this->response->ok($exist, ['entity_basic', 'user_basic']);
        }

        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $exist);

        if($isAuthorized === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }
        return $this->response->ok($exist, ['entity_basic', 'user_basic']);
    }

    /**
     * @Route(
     *     "entity/{entity_name}",
     *      requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="post_entity",
     *      methods={"POST"})
     */
    public function postEntityAction($entity_name, Request $request)
    {
        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity_name);

        if(empty($entityConfig))
        {
            return $this->response->notFound("This route does not exists");
        }

        $stateConfig = $entityConfig['states']['__default'];

        if(!array_key_exists($request->getMethod(), $stateConfig['methods']))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $userRoles = $this->getUser()->getRoles();
        $isAllowed = false;

        foreach ($userRoles as $role)
        {
            if(in_array($role, $stateConfig['methods']['POST']['by']['roles']))
            {
                $isAllowed = true;
            }
        }

        if($isAllowed === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }

        if(empty($request->getContent()))
        {
            return $this->response->badRequest("Post content must not be empty");
        }

        $entityToPost = new Entity();
        $entityToPost->setKind($entity_name);
        $entityToPost->setOwner($this->getUser());
        $entityToPost->setValidationState("__default");

        $formFields = $stateConfig['methods'][$request->getMethod()]['properties'];

        $posted = $this->processForm->generateAndProcess($formKind = 'post', $request->getContent(), $entityToPost, $entityConfig, $formFields);

        if(is_object($posted))
        {
            return $posted;
        }

        $state = $this->validator->processValidation($entityToPost,$entityToPost->getValidationState(), $entityConfig['states'], $this->getUser());

        if(!is_array($state) || $state['state'] != $entityToPost->getValidationState())
        {
            $this->em->flush();
        }

        if(is_array($state))
        {
            return $this->response->conflict($state['errors'],$entityToPost, ['entity_property', "entity_basic"]);
        }

        return $this->response->created($entityToPost, ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entity/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="patch_entity",
     *      methods={"PATCH"})
     */
    public function patchEntityAction($id, Request $request)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s");
        }

        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $state = $entity->getValidationState();

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind());
        $stateConfig = $entityConfig['states'][$state];

        if(!isset($stateConfig['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $constraints = $stateConfig['methods'][$request->getMethod()]['by'];

        $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);

        if($isAuthorized === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }

        $formFields = $stateConfig['methods'][$request->getMethod()]['properties'];

        $patched = $this->processForm->generateAndProcess($formKind = 'patch', $request->getContent(), $entity, $entityConfig, $formFields);

        if(empty($patched))
        {
            return $this->response->badRequest("Request content must not be empty");
        }

        if(is_object($patched))
        {
            return $patched;
        }

        $state = $this->validator->processValidation($entity,$entity->getValidationState(), $entityConfig['states'], $this->getUser());
        $this->em->flush();

        if(is_array($state))
        {
            return $this->response->conflict($state['errors'],$entity, ['entity_complete', 'user_basic']);
        }
        $entity->setValidationState($state);


        return $this->response->ok($entity, ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entity/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="delete_entity",
     *     methods={"DELETE"})
     */
    public function deleteEntityAction($id, Request $request)
    {

        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($entity))
        {
            return $this->response->notFound("The entity with the id $id does not exist");
        }

        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $state = $entity->getValidationState();

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind());
        $stateConfig = $entityConfig['states'][$state];


        if(!isset($stateConfig['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $constraints = $stateConfig['methods'][$request->getMethod()]['by'];

        $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);

        if($isAuthorized === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }


        $conflict_errors = $this->ruleManager->decideConflict($entity, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not delete this entity", $conflict_errors);
        }

        $this->em->remove($entity);
        $this->em->flush();
        return $this->response->empty();
    }

}
