<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Form\PropertyType;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RuleManager;
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
                                RuleManager $ruleManager)
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
            return $this->response->notFound("This route does not exists%s", "");
        }

        $access_errors = $this->ruleManager->decideAccess($exists, $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exists, $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

        $properties= $this->em->getRepository(Property::class)->findByKind($exists);

        return $this->response->ok($properties);
    }

    /**
     * @Route(
     *     "property/{property_name}/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="get_property",
     *     methods={"GET"})
     */
    public function getPropertyAction($property_name, $id, Request $request)
    {
        $property = $this->schemaLoader->loadPropertyEnumeration($property_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $this->em->getRepository(Property::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $property_name with the id %s does not exist", $id);
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }


        return $this->response->ok($exist);
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
                "kind" => strtoupper($entity_name)
            ]
        );

        if(empty($entity))
        {
            return $this->response->notFound("The $entity_name with the id %s was not found", $id);
        }

        $propertyList = $this->schemaLoader->loadPropertyEnumeration();
        $propertyExist = $this->schemaLoader->loadPropertyEnumeration($property_name, true);


        if(!in_array($propertyExist, array_keys($propertyList)))
        {
            return $this->response->notFound("The property named %s does not exist", $property_name);
        }

        $property = new Property();
        $propertyType = new \ReflectionClass(PropertyType::class);
        $posted = $this->processForm->process($request, $propertyType->getName(), $property);

        if(!is_a($posted, Property::class))
        {
            return $posted;
        }

        $property->setEntity($entity);
        $property->setKind($propertyExist);

        $access_errors = $this->ruleManager->decideAccess($posted, $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($posted, $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

        $this->em->persist($property);
        $this->em->flush();

        return $this->response->created($property);
    }

    /**
     * @Route(
     *     "property/{property_name}/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "property_name" = "^(\w{1,50})$"
     *      },
     *     name="delete_property",
     *     methods={"DELETE"})
     */
    public function deletePropertyAction($property_name, $id, Request $request)
    {
        $exist = $this->em->getRepository(Property::class)->findOneBy(
            [
                "uuid"=>$id,
                "kind"=>strtoupper($property_name)
            ]
        );

        if(empty($exist))
        {
            return  $this->response->notFound("The $property_name with the id %s was not found", $id);
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

        $this->em->remove($exist);
        $this->em->flush();
        return $this->response->empty();
    }

}
