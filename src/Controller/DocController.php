<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Property controller.
 *
 * @Route("api/")
 */
class DocController extends BaseController
{
    /**
     * @Route(
     *     "doc/entities",
     *     name="get_entities_doc",
     *      methods={"GET"})
     */
    public function getEntitiesDocAction(Request $request)
    {
        $entities = $this->schemaLoader->loadEntityEnumeration();
        return $this->response->ok($entities);
    }

    /**
     * @Route(
     *     "doc/entity/{entity_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entity_doc",
     *      methods={"GET"})
     */
    public function getEntityDocAction(string $entity_name, Request $request)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("Resource not found");
        }

        $state= $request->query->get("state");
        $properties = $request->query->get("properties");

        if(!empty($properties))
        {
            $propertyList = [];
            foreach($entity['properties'] as $property)
            {
                $propertyList[$property] = $this->schemaLoader->loadPropertyEnumeration($property);
            }
            return $this->response->ok($propertyList);
        }

        if(!empty($state))
        {
            if(!array_key_exists($state, $entity['states']))
            {
                return $this->response->notFound("Resource not found");
            }

            return $this->response->ok($entity['states'][$state]);
        }

        return $this->response->ok($entity);
    }

    /**
     * @Route(
     *     "doc/properties",
     *     name="get_properties_doc",
     *      methods={"GET"})
     */
    public function getPropertiesDocAction(Request $request)
    {
        $properties = $this->schemaLoader->loadPropertyEnumeration();
        return $this->response->ok($properties);
    }

    /**
     * @Route(
     *     "doc/property/{property_name}",
     *     requirements={
     *          "property_name" = "^(\w{1,50})$"
     *     },
     *     name="get_property_doc",
     *      methods={"GET"})
     */
    public function getPropertyDocAction(string $property_name, Request $request)
    {
        $property = $this->schemaLoader->loadPropertyEnumeration($property_name);
        if(empty($property))
        {
            return $this->response->notFound("Resource not found");
        }

        return $this->response->ok($property);
    }

    /**
     * @Route(
     *     "doc/enumerations",
     *     name="get_enumerations_doc",
     *      methods={"GET"})
     */
    public function getEnumerationsDocAction(Request $request)
    {
        $enumerations = $this->schemaLoader->loadEnumerationEnumeration();
        return $this->response->ok($enumerations);
    }

    /**
     * @Route(
     *     "doc/enumeration/{enumeration_name}",
     *     requirements={
     *          "enumeration_name" = "^(\w{1,50})$"
     *     },
     *     name="get_enumeration_doc",
     *      methods={"GET"})
     */
    public function getEnumerationDocAction(string $enumeration_name, Request $request)
    {
        $enumeration = $this->schemaLoader->loadEnumerationEnumeration($enumeration_name);
        if(empty($enumeration))
        {
            return $this->response->notFound("Resource not found");
        }

        return $this->response->ok($enumeration);
    }
}
