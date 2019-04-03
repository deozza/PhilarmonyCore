<?php
namespace App\Controller;

use App\Entity\EntityPost;
use App\Entity\PropertyPost;
use App\Entity\TypePost;
use App\Form\EntityEnumerationPostType;
use App\Form\PropertyEnumerationPostType;
use App\Form\TypeEnumerationPostType;
use App\Service\DatabaseSchemaLoader;
use App\Service\FormErrorSerializer;
use App\Service\ProcessForm;
use App\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Property controller.
 *
 * @Route("api/")
 */
class DatabaseSchemaController extends AbstractController
{
    public function __construct(ResponseMaker $responseMaker,
                                FormErrorSerializer $formErrorSerializer,
                                EntityManagerInterface $em,
                                ProcessForm $processForm,
                                DatabaseSchemaLoader $schemaLoader)
    {
        $this->response = $responseMaker;
        $this->formErrorSerializer = $formErrorSerializer;
        $this->em = $em;
        $this->processForm = $processForm;
        $this->schemaLoader = $schemaLoader;
    }

    /**
     * @Route("entity", name="get_entity_enumeration", methods={"GET"})
     */
    public function getEntityEnumerationAction()
    {
        $entities = $this->schemaLoader->loadEntityEnumeration();
        return $this->response->ok($entities);
    }

    /**
     * @Route("property", name="get_property_enumeration", methods={"GET"})
     */
    public function getPropertyEnumerationAction()
    {
        $properties = $this->schemaLoader->loadPropertyEnumeration();
        return $this->response->ok($properties);
    }

    /**
     * @Route("type", name="get_type_enumeration", methods={"GET"})
     */
    public function getTypeEnumerationAction()
    {
        $type = $this->schemaLoader->loadTypeEnumeration();
        return $this->response->ok($type);
    }

    /**
     * @Route("entity", name="post_entity_enumeration", methods={"POST"})
     */
    public function postEntityEnumerationAction(Request $request)
    {
        $properties = $this->schemaLoader->loadPropertyEnumeration();

        $entity = new EntityPost();
        $entityType = new \ReflectionClass(EntityEnumerationPostType::class);
        $posted = $this->processForm->process($request, $entityType->getName(), $entity, ['properties'=>array_keys($properties)]);

        if(!is_a($posted, EntityPost::class))
        {
            return $posted;
        }

        $entities = $this->schemaLoader->pushEntityEnumeration($posted);

        if($entities == false)
        {
            return $this->response->badRequest("An error happened while updating the database schema");
        }

        return $this->response->created($entities);
    }

    /**
     * @Route("property", name="post_property_enumeration", methods={"POST"})
     */
    public function postPropertyEnumerationAction(Request $request)
    {
        $types = $this->schemaLoader->loadTypeEnumeration();

        $property = new PropertyPost();
        $propertyType = new \ReflectionClass(PropertyEnumerationPostType::class);
        $posted = $this->processForm->process($request, $propertyType->getName(), $property, ['types'=>array_keys($types)]);

        if(!is_a($posted, PropertyPost::class))
        {
            return $posted;
        }

        $entities = $this->schemaLoader->pushPropertyEnumeration($posted);

        if($entities == false)
        {
            return $this->response->badRequest("An error happened while updating the database schema");
        }

        return $this->response->created($entities);
    }

    /**
     * @Route("type", name="post_type_enumeration", methods={"POST"})
     */
    public function postTypeEnumerationAction(Request $request)
    {
        $type = new TypePost();
        $TypeType = new \ReflectionClass(TypeEnumerationPostType::class);
        $posted = $this->processForm->process($request, $TypeType->getName(), $type);

        if(!is_a($posted, TypePost::class))
        {
            return $posted;
        }

        $types = $this->schemaLoader->pushTypeEnumeration($posted);

        if($types == false)
        {
            return $this->response->badRequest("An error happened while updating the database schema");
        }

        return $this->response->created($types);
    }

}
