<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Exceptions\BadFileTree;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyCoreBundle\Service\FormManager\ProcessForm;
use Deozza\ResponseMaker\Service\ResponseMaker;
use Deozza\PhilarmonyCoreBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyCoreBundle\Service\Validation\Validate;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
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
        try
        {
            $exists= $this->schemaLoader->loadEntityEnumeration($entity_name);
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if(empty($exists))
        {
            return $this->response->notFound("This route does not exists%s");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exists, $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to the the $entity_name", $conflict_errors);
        }

        $filter = $request->query->get("filterBy", []);
        $sort = $request->query->get("sortBy", []);
        try
        {
            $entitiesQuery = $this->em->getRepository(Entity::class)->findAllFiltered($filter, $sort, $entity_name);
        }
        catch(\Exception $e)
        {
            return $this->response->notFound("No entity was found with these filters");
        }

        try
        {
            foreach($entitiesQuery as $key=>$item)
            {
                if(!isset($exists['states']))
                {
                    throw new BadFileTree("$entity_name must have a 'states' node");
                }

                if(!isset($exists['states'][$item->getValidationState()]))
                {
                    throw new BadFileTree("State ".$item->getValidationState()." was not found in $entity_name");
                }

                if(!isset($exists['states'][$item->getValidationState()]['methods']))
                {
                    throw new BadFileTree("State ".$item->getValidationState()." of $entity_name must have a 'methods' node");
                }

                if(!isset($exists['states'][$item->getValidationState()]['methods'][$request->getMethod()]))
                {
                    unset($entitiesQuery[$key]);
                }

                if(!isset($exists['states'][$item->getValidationState()]['methods'][$request->getMethod()]['by']))
                {
                    throw new BadFileTree("Method ".$request->getMethod()." must have a 'by' node in $entity_name");
                }

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
        }
        catch (\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        $entities = $this->paginator->paginate(
            $entitiesQuery,
            $request->query->getInt("page", 1),
            $request->query->getInt("number", 10)
        );

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
    public function getEntityAction($id, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The entity with the id $id does not exist");
        }

        $state = $exist->getValidationState();

        try
        {
            $entityConfig = $this->schemaLoader->loadEntityEnumeration($exist->getKind());
            if(!isset($entityConfig['states']))
            {
                throw new BadFileTree($exist->getKind()." must have a 'states' node");
            }

            if(!isset($entityConfig['states'][$state]['methods']))
            {
                throw new BadFileTree("$state of ".$exist->getKind()." must have a 'methods' node");
            }

        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        try
        {
            if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]['by']))
            {
                throw new BadFileTree($request->getMethod(). "of $state of ".$exist->getKind()." must have a 'by' node");
            }

            $constraints = $entityConfig['states'][$state]['methods'][$request->getMethod()]['by'];
        }
        catch (\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if($constraints === "all")
        {
            return $this->response->ok($exist, ['entity_basic', 'user_basic']);
        }

        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        try
        {
            $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $exist);
            if($isAuthorized === false)
            {
                return $this->response->forbiddenAccess("Access to this resource is forbidden");
            }
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }

        $this->handleEvents($request->getMethod(), $entityConfig['states'][$state]['methods'][$request->getMethod()], $exist, $eventDispatcher);
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
    public function postEntityAction($entity_name, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        try
        {
            $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity_name);
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if(empty($entityConfig))
        {
            return $this->response->notFound("This route does not exists");
        }

        try
        {
            if(!isset($entityConfig['states']['__default']))
            {
                throw new BadFileTree("$entity_name must have a __default state");
            }

            $stateConfig = $entityConfig['states']['__default'];
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            if(!isset($stateConfig['methods']))
            {
                throw new BadFileTree("__default state of $entity_name must have a 'methods' node");
            }

            if(!array_key_exists($request->getMethod(), $stateConfig['methods']))
            {
                return $this->response->methodNotAllowed($request->getMethod());
            }
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $userRoles = $this->getUser()->getRoles();
        $isAllowed = false;

        try
        {
            if(!isset($stateConfig['methods']['POST']['by']))
            {
                throw new BadFileTree($request->getMethod()." must have a 'by' node");
            }

            if(isset($stateConfig['methods']['POST']['by']['roles']))
            {
                foreach ($userRoles as $role)
                {
                    if(in_array($role, $stateConfig['methods']['POST']['by']['roles']))
                    {
                        $isAllowed = true;
                    }
                }
            }
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
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

        try
        {
            if(!isset($stateConfig['methods'][$request->getMethod()]['properties']))
            {
                throw new BadFileTree($request->getMethod()." must have a 'properties' node");
            }

            $formFields = $stateConfig['methods'][$request->getMethod()]['properties'];
            $posted = $this->processForm->generateAndProcess($formKind = 'post', $request->getContent(), $entityToPost, $entityConfig, $formFields);

            if(is_object($posted))
            {
                return $posted;
            }

            $state = $this->validator->processValidation($entityToPost,$entityToPost->getValidationState(), $entityConfig['states'], $this->getUser());

        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }
        $conflict_errors = $this->ruleManager->decideConflict($entityToPost , $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }
        if(!is_array($state) || $entityToPost->getValidationState() != "__default")
        {
            $this->handleEvents($request->getMethod(), $stateConfig, $entityToPost, $eventDispatcher);
            $this->em->flush();
        }

        if(is_array($state))
        {
            return $this->response->conflict($state['errors'],$entityToPost, ['entity_id', 'entity_property', "entity_basic"]);
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
    public function patchEntityAction($id, Request $request, EventDispatcherInterface $eventDispatcher)
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

        try
        {
            $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind());
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            if(!isset($entityConfig['states'][$state]))
            {
                throw new BadFileTree($entity->getKind()." must have a $state state");
            }
            $stateConfig = $entityConfig['states'][$state];
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if(!isset($stateConfig['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        try
        {
            if(!isset($stateConfig['methods'][$request->getMethod()]['by']))
            {
                throw new BadFileTree($request->getMethod()." must have a 'by' node");
            }
            $constraints = $stateConfig['methods'][$request->getMethod()]['by'];
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);
            if($isAuthorized === false)
            {
                return $this->response->forbiddenAccess("Access to this resource is forbidden");
            }
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            if(!isset($stateConfig['methods'][$request->getMethod()]['properties']))
            {
                throw new BadFileTree($request->getMethod()." must have a 'properties' node");
            }
            $formFields = $stateConfig['methods'][$request->getMethod()]['properties'];
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
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

            $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher);
            $this->em->flush();

        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

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
    public function deleteEntityAction($id, Request $request, EventDispatcherInterface $eventDispatcher)
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

        try
        {
            $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind());
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            if(!isset($entityConfig['states']))
            {
                throw new BadFileTree($entity->getKind()." must have a 'states' node");
            }
            $stateConfig = $entityConfig['states'][$state];

        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            if (!isset($stateConfig["methods"]))
            {
                throw new BadFileTree("$state of ".$entity->getKind()." must have a 'methods' node");
            }
            if(!isset($stateConfig['methods'][$request->getMethod()]))
            {
                return $this->response->methodNotAllowed($request->getMethod());
            }

            if(!isset($stateConfig['methods'][$request->getMethod()]['by']))
            {
                throw new BadFileTree($request->getMethod()." of $state must have a 'by' node");
            }
            $constraints = $stateConfig['methods'][$request->getMethod()]['by'];
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        try
        {
            $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);
            if($isAuthorized === false)
            {
                return $this->response->forbiddenAccess("Access to this resource is forbidden");
            }
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        $conflict_errors = $this->ruleManager->decideConflict($entity, $request->getContent(), $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not delete this entity", $conflict_errors);
        }

        $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher);

        $this->em->remove($entity);
        $this->em->flush();
        return $this->response->empty();
    }

    private function handleEvents($method, $stateConfig, $entity, $eventDispatcher)
    {
        if(isset($stateConfig['methods'][$method]['post_scripts']))
        {
            $scripts = $stateConfig['methods'][$method]['post_scripts'];

            $event = new GenericEvent($entity);
            foreach($scripts as $script)
            {
                $eventDispatcher->dispatch($script, $event);
            };
        }
    }

}
