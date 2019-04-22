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
     *     "property/{property_name}",
     *     requirements={
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="get_property_list",
     *      methods={"GET"})
     */
    public function getPropertyListAction($property_name, Request $request)
    {
        $exists= $this->schemaLoader->loadPropertyEnumeration($property_name, true);

        if(empty($exists))
        {
            return $this->response->notFound("This route does not exists%s");
        }

        $access_errors = $this->ruleManager->decideAccess($exists, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exists, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

        $properties= $this->em->getRepository(Property::class)->findByKind($exists);

        return $this->response->ok($properties, ['property_complete', "entity_id"]);
    }

    /**
     * @Route(
     *     "property/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="get_property",
     *     methods={"GET"})
     */
    public function getPropertyAction($id, Request $request)
    {

        $exist = $this->em->getRepository(Property::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The property with the id $id does not exist");
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }


        return $this->response->ok($exist, ['property_basic', "entity_id"]);
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

        if(!array_key_exists('multiple', $property) || !$property['multiple'])
        {
            $alreadyExists = $this->em->getRepository(Property::class)->findBy(
                [
                    "entity" => $entity->getId(),
                    'kind' => $property_name
                ]
            );

            if(!empty($alreadyExists))
            {
                return $this->response->forbiddenAccess("$entity_name already have a $property_name");
            }
        }


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
     *     "property/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="patch_property",
     *      methods={"PATCH"})
     */
    public function patchPropertyAction($id, Request $request)
    {
        $property = $this->em->getRepository(Property::class)->findOneByUuid($id);

        if(empty($property))
        {
            return $this->response->notFound("The property with the id $id was not found");
        }

        $patched = $this->processForm->generateAndProcess($formKind = "patch", $request->getContent(), $property,null,  [$property->getKind()]);

        if(is_object($patched))
        {
            return $patched;
        }

        $property->setValue($patched['value']);

        $access_errors = $this->ruleManager->decideAccess($patched, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not patch this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($patched, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not patch this property", $conflict_errors);
        }

        $this->em->flush();

        return $this->response->created($property, ['property_basic', "entity_id"]);
    }

    /**
     * @Route(
     *     "property/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="delete_property",
     *     methods={"DELETE"})
     */
    public function deletePropertyAction($id, Request $request)
    {
        $exist = $this->em->getRepository(Property::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return  $this->response->notFound("The property with the id $id was not found");
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not delete this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not delete this property", $conflict_errors);
        }

        $this->em->remove($exist);
        $this->em->flush();
        return $this->response->empty();
    }

}
