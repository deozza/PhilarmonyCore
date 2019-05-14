<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\FormManager\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyBundle\Service\Validation\Validate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Property controller.
 *
 * @Route("api/")
 */
class PropertyController extends AbstractController
{
    public function __construct(ResponseMaker $responseMaker,
                                EntityManagerInterface $em,
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
        $this->validator = $validate;

    }

    /**
     * @Route(
     *     "{entity_name}/{id}/{property_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$",
     *          "property_name" = "^(\w{1,50})$",
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="get_property_from_entity",
     *     methods={"GET"})
     */
    public function getPropertyFromEntityAction($entity_name,$property_name, $id, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneBy([
            "uuid" => $id,
            "kind" => $entity_name
        ]);

        if(empty($entity))
        {
            return $this->response->notFound("Either the $entity_name was not found or the $property_name with the id $id was not found");
        }

        if(!array_key_exists($property_name, $entity->getProperties()))
        {
            return $this->response->notFound("There is no $property_name in $entity_name");
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

        if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $constraints = $entityConfig['states'][$state]['methods'][$request->getMethod()]['by'];

        if($constraints !== "all")
        {
            if(empty($this->getUser()->getUsername()))
            {
                return $this->response->notAuthorized();
            }

            $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);

            if($isAuthorized === false)
            {
                return $this->response->forbiddenAccess("Access to this resource is forbidden");
            }
        }

        $propertiesOfEntity = $entity->getProperties();

        $key = $request->query->get("key");

        if(empty($key))
        {
            $key = "all";
        }

        $data = $propertiesOfEntity[$property_name];


        if($key !== "all")
        {
            $data = $propertiesOfEntity[$property_name][$key];
        }
        $this->handleEvents($request->getMethod(), $entityConfig['states'][$state]['methods'][$request->getMethod()], $entity, $eventDispatcher);
        return $this->response->ok($data);
    }


    /**
     * @Route(
     *     "{entity_name}/{id}/{property_name}",
     *      requirements={
     *          "entity_name" = "^(\w{1,50})$",
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="post_property",
     *      methods={"POST"})
     */
    public function postPropertyAction($entity_name, $id, $property_name, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy(
            [
                "uuid" => $id,
                "kind" => $entity_name
            ]
        );

        if(empty($entity))
        {
            return $this->response->notFound("The $entity_name with the id $id was not found");
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

        if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]) ||
            !in_array($property_name, $entityConfig['states'][$state]['methods'][$request->getMethod()]['properties']))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        if(empty($request->getContent()))
        {
            return $this->response->badRequest("Post content must not be empty");
        }

        $stateConfig = $entityConfig['states'][$state];
        $constraints = $stateConfig['methods'][$request->getMethod()]['by'];

        $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);

        if($isAuthorized === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }

        $posted = $this->processForm->generateAndProcess($formKind = "post", $request->getContent(), $entity,null,  [$property_name]);

        if(is_object($posted))
        {
            return $posted;
        }

        $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher);
        $this->em->flush();

        return $this->response->created($entity, ['entity_complete']);
    }

    /**
     * @Route(
     *     "{entity_name}/{id}/{property_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$",
     *          "property_name" = "^(\w{1,50})$",
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="patch_property",
     *      methods={"PATCH"})
     */
    public function patchPropertyAction($entity_name, $property_name, $id, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy([
            "uuid" => $id,
            "kind" => $entity_name
        ]);

        if(empty($entity))
        {
            return $this->response->notFound("Either the $entity_name was not found or the $property_name with the id $id was not found");
        }

        if(!array_key_exists($property_name, $entity->getProperties()))
        {
            return $this->response->notFound("There is no $property_name in $entity_name");
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

        if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        if($entityConfig['states'][$state]['methods'][$request->getMethod()]['properties'] !== "all" &&
            !in_array($property_name, $entityConfig['states'][$state]['methods'][$request->getMethod()]['properties']))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        if(empty($request->getContent()))
        {
            return $this->response->badRequest("Post content must not be empty");
        }

        $stateConfig = $entityConfig['states'][$state];
        $constraints = $stateConfig['methods'][$request->getMethod()]['by'];

        $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);

        if($isAuthorized === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }

        $patched = $this->processForm->generateAndProcess($formKind = "patch", $request->getContent(), $entity,null,  [$property_name]);

        if(is_object($patched))
        {
            return $patched;
        }
        $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher);
        $this->em->flush();

        return $this->response->ok($patched);
    }

    /**
     * @Route(
     *     "{entity_name}/{id}/{property_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$",
     *          "property_name" = "^(\w{1,50})$",
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="delete_property",
     *     methods={"DELETE"})
     */
    public function deletePropertyAction($entity_name,$property_name, $id, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        if(empty($this->getUser()->getUsername()))
        {
            return $this->response->notAuthorized();
        }

        $entity = $this->em->getRepository(Entity::class)->findOneBy([
            "uuid" => $id,
            "kind" => $entity_name
        ]);

        if(empty($entity))
        {
            return $this->response->notFound("Either the $entity_name was not found or the $property_name with the id $id was not found");
        }

        if(!array_key_exists($property_name, $entity->getProperties()))
        {
            return $this->response->notFound("There is no $property_name in $entity_name");
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

        if(!isset($entityConfig['states'][$state]['methods'][$request->getMethod()]))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        if($entityConfig['states'][$state]['methods'][$request->getMethod()]['properties'] !== "all" &&
            !in_array($property_name, $entityConfig['states'][$state]['methods'][$request->getMethod()]['properties']))
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $stateConfig = $entityConfig['states'][$state];
        $constraints = $stateConfig['methods'][$request->getMethod()]['by'];

        $isAuthorized = $this->validator->validateUserPermission($constraints, $this->getUser(), $entity);
        if($isAuthorized === false)
        {
            return $this->response->forbiddenAccess("Access to this resource is forbidden");
        }

        $propertiesOfEntity = $entity->getProperties();

        $key = $request->query->get("key");

        if(empty($key))
        {
            $key = "all";
        }

        if($key === "all")
        {
            unset($propertiesOfEntity[$property_name]);
        }
        else
        {
            if(!isset($propertiesOfEntity[$property_name][$key]))
            {
                return $this->response->notFound("The $entity_name with the $property_name and the key $id was not found");
            }
            unset($propertiesOfEntity[$property_name][$key]);
        }

        try
        {
            $property = $this->schemaLoader->loadPropertyEnumeration($property_name);
        }
        catch(\Exception $e)
        {
            return $this->response->badRequest($e->getMessage());
        }

        if(array_key_exists("default", $property) && $property['default'] !== null)
        {
            $default = explode('.', $property['default']);

            $defaultValue = $default[0];

            if($defaultValue === "date")
            {
                $defaultValue = new \DateTime($default[1]);
            }
            $propertiesOfEntity[$property_name] = $defaultValue;
        }

        $entity->setProperties($propertiesOfEntity);

        $this->handleEvents($request->getMethod(), $stateConfig, $entity, $eventDispatcher);

        $this->em->flush();
        return $this->response->empty();
    }
    private function handleEvents($request, $stateConfig, $entity, $eventDispatcher)
    {
        if(isset($stateConfig['methods'][$request->getMethod()]['post_scripts']))
        {
            $scripts = $stateConfig['methods'][$request->getMethod()]['post_scripts'];

            $event = new GenericEvent($entity);
            foreach($scripts as $script)
            {
                $eventDispatcher->dispatch($script, $event);
            };
        }
    }
}
