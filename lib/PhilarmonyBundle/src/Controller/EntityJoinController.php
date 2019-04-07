<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\EntityJoin;
use Deozza\PhilarmonyBundle\Form\EntityJoinType;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RuleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * EntityJoin controller.
 *
 * @Route("api/")
 */
class EntityJoinController extends AbstractController
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
     *     "entityjoin/{entityjoin_name}",
     *     requirements={
     *          "entityjoin_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entityjoin_list",
     *      methods={"GET"})
     */
    public function getEntityJoinListAction($entityjoin_name, Request $request)
    {
        $exists= $this->schemaLoader->loadEntityJoinEnumeration($entityjoin_name, true);

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


        $entityJoins = $this->em->getRepository(EntityJoin::class)->findByKind($exists);

        return $this->response->ok($entityJoins);
    }

    /**
     * @Route(
     *     "entityjoin/{entityjoin_name}/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "entityjoin_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entityjoin",
     *     methods={"GET"})
     */
    public function getEntityJoinAction($entityjoin_name, $id, Request $request)
    {
        $entityjoin = $this->schemaLoader->loadEntityJoinEnumeration($entityjoin_name);
        if(empty($entityjoin))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $this->em->getRepository(EntityJoin::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entityjoin_name with the id %s does not exist", $id);
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
     *     "entityjoin/{entityjoin_name}",
     *      requirements={
     *          "entityjoin_name" = "^(\w{1,50})$"
     *     },
     *     name="post_entityjoin",
     *      methods={"POST"})
     */
    public function postEntityJoinAction($entityjoin_name, Request $request)
    {
        $entityjoin = $this->schemaLoader->loadEntityJoinEnumeration();
        $entityjoin_name = strtoupper($entityjoin_name);

        if(array_key_exists($entityjoin_name, $entityjoin) === false)
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $access_errors = $this->ruleManager->decideAccess($entityjoin[$entityjoin_name], $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this property");
        }

        $newEntityJoin = new EntityJoin();
        $newEntityJoin->setKind($entityjoin_name);
        $newEntityJoin->setOwner("Toto");

        $entityjoinType = new \ReflectionClass(EntityJoinType::class);
        $posted = $this->processForm->process($request, $entityjoinType->getName(), $newEntityJoin);

        if(!is_a($posted, EntityJoin::class))
        {
            return $posted;
        }

        $entity1 = $this->em->getRepository(Entity::class)->findOneBy(
            [
                "kind" => $entityjoin[$entityjoin_name]['ENTITY1'],
                "uuid" => $posted->getEntity1uuid()
            ]
        );

        if(empty($entity1))
        {
            return $this->response->badRequest("The first entity of the relation is not valid");
        }

        $posted->setEntity1($entity1);

        if(array_key_exists('ENTITY2', $entityjoin[$entityjoin_name]))
        {

            $entity2 = $this->em->getRepository(Entity::class)->findOneBy(
                [
                    "kind" => $entityjoin['ENTITY2'],
                    "uuid" => $posted->getEntity2uuid()
                ]
            );
            if(empty($entity2))
            {
                return $this->response->badRequest("The second entity of the relation is not valid");
            }

            $posted->setEntity2($entity2);
        }

        $conflict_errors = $this->ruleManager->decideConflict($posted, $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this property", $conflict_errors);
        }

        $this->em->persist($posted);
        $this->em->flush();

        return $this->response->created($posted);
    }

    /**
     * @Route(
     *     "entity/{entityjoin_name}/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "entityjoin_name" = "^(\w{1,50})$"
     *      },
     *     name="delete_entityjoin",
     *     methods={"DELETE"})
     */
    public function deleteEntityJoinAction($entityjoin_name, $id, Request $request)
    {
        $entityjoin = $this->schemaLoader->loadEntityEnumeration($entityjoin_name);
        if(empty($entityjoin))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $this->em->getRepository(EntityJoin::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entityjoin_name with the id %s does not exist", $id);
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
