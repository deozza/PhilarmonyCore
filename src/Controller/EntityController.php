<?php
namespace App\Controller;

use App\Entity\Entity;
use App\Repository\EntityRepository;
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
class EntityController extends AbstractController
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
     * @Route("{entity_name}", name="get_entity_list", methods={"GET"})
     */
    public function getEntityListAction($entity_name)
    {
        $entity= $this->schemaLoader->loadEntityEnumeration($entity_name);

        if(empty($entity))
        {
            return $this->response->notFound("This route does not exists%s", "");
        }

        return $this->response->ok($entity);
    }

    /**
     * @Route(
     *     "{entity_name}/{id}",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     name="get_entity",
     *     methods={"GET"})
     */
    public function getEntityAction($entity_name, $id, EntityRepository $repository)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $repository->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entity_name with the id %s does not exist", $id);
        }

        return $this->response->ok($exist);
    }

    /**
     * @Route("{entity_name}", name="post_entity", methods={"POST"})
     */
    public function postEntityAction($entity_name)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name, true);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $newEntity = new Entity();
        $newEntity->setKind($entity);
        $newEntity->setOwner("Toto");
        $this->em->persist($newEntity);
        $this->em->flush();

        return $this->response->created($newEntity);
    }

    /**
     * @Route(
     *     "{entity_name}/{id}",
     *     requirements={"id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"},
     *     name="get_entity",
     *     methods={"DELETE"})
     */
    public function deleteEntityAction($entity_name, $id, EntityRepository $repository)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $repository->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entity_name with the id %s does not exist", $id);
        }

        $propertiesLinked = $exist->getProperties();

        if(!empty($propertiesLinked))
        {
            foreach ($propertiesLinked as $property)
            {
                $this->em->remove($property);
            }
        }

        $this->em->remove($exist);
        $this->em->flush();
        return $this->response->empty();
    }

}
