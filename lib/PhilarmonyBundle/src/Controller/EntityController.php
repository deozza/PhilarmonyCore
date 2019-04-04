<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                                ProcessForm $processForm,
                                DatabaseSchemaLoader $schemaLoader)
    {
        $this->response = $responseMaker;
        $this->em = $em;
        $this->processForm = $processForm;
        $this->schemaLoader = $schemaLoader;
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
    public function getEntityListAction($entity_name)
    {
        $exists= $this->schemaLoader->loadEntityEnumeration($entity_name, true);

        if(empty($exists))
        {
            return $this->response->notFound("This route does not exists%s", "");
        }

        $entities = $this->em->getRepository(Entity::class)->findByKind($exists);

        return $this->response->ok($entities);
    }

    /**
     * @Route(
     *     "entity/{entity_name}/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entity",
     *     methods={"GET"})
     */
    public function getEntityAction($entity_name, $id)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entity_name with the id %s does not exist", $id);
        }

        return $this->response->ok($exist);
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
     *     "entity/{entity_name}/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "entity_name" = "^(\w{1,50})$"
     *      },
     *     name="delete_entity",
     *     methods={"DELETE"})
     */
    public function deleteEntityAction($entity_name, $id)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

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
