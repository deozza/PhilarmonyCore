<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RuleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
     *     "entity/{entity_name}",
     *     requirements={
     *          "entity_name" = "^(\w{1,50})$"
     *     },
     *     name="get_entity_list",
     *      methods={"GET"})
     */
    public function getEntityListAction($entity_name, Request $request)
    {
        $exists= $this->schemaLoader->loadEntityEnumeration($entity_name, true);

        if(empty($exists))
        {
            return $this->response->notFound("This route does not exists%s");
        }

        $access_errors = $this->ruleManager->decideAccess($exists, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not access to the $entity_name");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exists, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to the the $entity_name", $conflict_errors);
        }


        $entities = $this->em->getRepository(Entity::class)->findByKind($exists);

        return $this->response->ok($entities, ['entity_complete', 'property_complete']);
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
    public function getEntityAction($entity_name, $id, Request $request)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s");
        }

        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entity_name with the id $id does not exist");
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not access to this $entity_name");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this $entity_name", $conflict_errors);
        }


        return $this->response->ok($exist, ['entity_basic', 'property_complete']);
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
    public function postEntityAction($entity_name, Request $request)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);

        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s");
        }

        $access_errors = $this->ruleManager->decideAccess($entity, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not add this $entity_name");
        }

        if(!$entity['post'])
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $entityToPost = new Entity();
        $entityToPost->setKind($entity_name);
        $entityToPost->setOwner($request->getUser());

        $posted = $this->processForm->generateAndProcess($formKind = 'post', $request->getContent(), $entityToPost, $entity);

        $conflict_errors = $this->ruleManager->decideConflict($entity, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this $entity_name", $conflict_errors);
        }

        if(is_object($posted))
        {
            return $posted;
        }

        $this->em->flush();

        return $this->response->created($entityToPost, ['entity_complete', 'property_complete']);
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
    public function deleteEntityAction($entity_name, $id, Request $request)
    {
        $entity = $this->schemaLoader->loadEntityEnumeration($entity_name);
        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s", "");
        }

        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The $entity_name with the id $id does not exist");
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not delete this $entity_name");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not delete this $entity_name", $conflict_errors);
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
