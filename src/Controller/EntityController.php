<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\FormManager\ProcessForm;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Deozza\PhilarmonyBundle\Service\RulesManager\RulesManager;
use Deozza\PhilarmonyBundle\Service\Validation\Validate;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                                PaginatorInterface $paginator,
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
        $this->paginator = $paginator;
        $this->validator = $validate;
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


        $propertyFilter = $request->query->get("property", []);
        $entityFilter = $request->query->get("entity", []);
        try
        {
            $entitiesQuery = $this->em->getRepository(Entity::class)->findAllFiltered($propertyFilter, $entityFilter, $exists);
            $entities = $this->paginator->paginate(
                $entitiesQuery,
                $request->query->getInt("page", 1),
                $request->query->getInt("number", 10)
            );

        }
        catch(\Exception $e)
        {
            return $this->response->notFound("No entity was found with these filters");
        }

        return $this->response->okPaginated($entities, ['entity_complete']);
    }

    /**
     * @Route(
     *     "entity/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *     },
     *     name="get_entity",
     *     methods={"GET"})
     */
    public function getEntityAction($id, Request $request)
    {
        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The entity with the id $id does not exist");
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not access to this entity");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not access to this entity", $conflict_errors);
        }


        return $this->response->ok($exist, ['entity_basic']);
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

        $isValid = $this->validator->processValidation($entityToPost);

        die;
/*
        if(is_array($isValid))
        {
            $posted->setValidationState(false);
        }

        $posted->setValidationState($isValid);
        $this->em->flush();

        if($isValid === false)
        {
            return $this->response->conflict("To pass to the next validation state, you need to correct : ", $isValid['context']);
        }
*/
        return $this->response->created($entityToPost, ['entity_complete']);
    }

    /**
     * @Route(
     *     "entity/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="patch_entity",
     *      methods={"PATCH"})
     */
    public function patchEntityAction($id, Request $request)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($entity))
        {
            return $this->response->notFound("This route does not exist%s");
        }

        $access_errors = $this->ruleManager->decideAccess($entity, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not patch this $entity->getKind()");
        }


        $entityProperties = $this->schemaLoader->loadEntityEnumeration($entity->getKind());


        if(!$entityProperties['patch'])
        {
            return $this->response->methodNotAllowed($request->getMethod());
        }

        $patched = $this->processForm->generateAndProcess($formKind = 'patch', $request->getContent(), $entity, $entityProperties);

        $conflict_errors = $this->ruleManager->decideConflict($entity, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not add this $entity->getKind()", $conflict_errors);
        }

        if(is_object($patched))
        {
            return $patched;
        }

        $this->em->flush();

        return $this->response->ok($entity, ['entity_complete']);
    }

    /**
     * @Route(
     *     "entity/{id}",
     *     requirements={
     *          "id" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *      },
     *     name="delete_entity",
     *     methods={"DELETE"})
     */
    public function deleteEntityAction($id, Request $request)
    {

        $exist = $this->em->getRepository(Entity::class)->findOneByUuid($id);

        if(empty($exist))
        {
            return $this->response->notFound("The entity with the id $id does not exist");
        }

        $access_errors = $this->ruleManager->decideAccess($exist, $request->getMethod());

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not delete this entity");
        }

        $conflict_errors = $this->ruleManager->decideConflict($exist, $request->getMethod(),__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not delete this entity", $conflict_errors);
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
