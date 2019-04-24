<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\FormManager\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RulesManager\RulesManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                                RulesManager $ruleManager)
    {
        $this->response = $responseMaker;
        $this->em = $em;
        $this->processForm = $processForm;
        $this->schemaLoader = $schemaLoader;
        $this->ruleManager = $ruleManager;
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
    public function postPropertyAction($entity_name, $id, $property_name, Request $request)
    {
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

        $entityPostableProperties = $this->schemaLoader->loadEntityEnumeration($entity_name);


        if($entityPostableProperties['post']['properties'] === "all")
        {
            if(!in_array($property_name, $entityPostableProperties['properties']))
            {
                return $this->response->notFound("$entity_name does not have a property called $property_name");
            }
        }
        else
        {
            if(!in_array($property_name, $entityPostableProperties['post']['properties']))
            {
                {
                    return $this->response->notFound("$entity_name does not have a property called $property_name");
                }
            }
        }

        $property = $this->schemaLoader->loadPropertyEnumeration($property_name);


        $posted = $this->processForm->generateAndProcess($formKind = "post", $request->getContent(), $entity,null,  [$property_name]);

        if(is_object($posted))
        {
            return $posted;
        }

        $access_errors = $this->ruleManager->decideAccess($posted, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($posted, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

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
    public function patchPropertyAction($entity_name, $property_name, $id, Request $request)
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

        $entityPostableProperties = $this->schemaLoader->loadEntityEnumeration($entity_name);

        if($entityPostableProperties['patch']['properties'] === "all")
        {
            if(!in_array($property_name, $entityPostableProperties['properties']))
            {
                return $this->response->methodNotAllowed($request->getMethod());
            }
        }
        else
        {
            if(!in_array($property_name, $entityPostableProperties['patch']['properties']))
            {
                    return $this->response->methodNotAllowed($request->getMethod());
            }
        }

        $patched = $this->processForm->generateAndProcess($formKind = "patch", $request->getContent(), $entity,null,  [$property_name]);


        if(is_object($patched))
        {
            return $patched;
        }

        $access_errors = $this->ruleManager->decideAccess($patched, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($patched, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

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
    public function deletePropertyAction($entity_name,$property_name, $id, Request $request)
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

        $access_errors = $this->ruleManager->decideAccess($entity, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not delete this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($entity, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not delete this property", $conflict_errors);
        }

        $property = $this->schemaLoader->loadPropertyEnumeration($property_name);

        if($property['required'])
        {
            return $this->response->forbiddenAccess("$property_name can not be deleted. It is a required property");
        }

        $propertiesOfEntity = $entity->getProperties();

        unset($propertiesOfEntity[$property_name]);

        if($property['default'] !== null)
        {
            $propertiesOfEntity[$property_name] = $property['default'];
        }

        $entity->setProperties($propertiesOfEntity);

        $this->em->flush();
        return $this->response->empty();
    }

}
