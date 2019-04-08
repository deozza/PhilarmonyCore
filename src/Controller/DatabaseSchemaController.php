<?php
namespace Deozza\PhilarmonyBundle\Controller;

use Deozza\PhilarmonyBundle\Entity\EntityJoinPost;
use Deozza\PhilarmonyBundle\Entity\EntityPost;
use Deozza\PhilarmonyBundle\Entity\PropertyPost;
use Deozza\PhilarmonyBundle\Entity\TypePost;
use Deozza\PhilarmonyBundle\Form\EntityEnumerationPostType;
use Deozza\PhilarmonyBundle\Form\EntityJoinEnumerationPostType;
use Deozza\PhilarmonyBundle\Form\PropertyEnumerationPostType;
use Deozza\PhilarmonyBundle\Form\TypeEnumerationPostType;
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
 * @Route("api/databaseSchema/")
 */
class DatabaseSchemaController extends AbstractController
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
     * @Route("entity", name="get_entity_enumeration", methods={"GET"})
     */
    public function getEntityEnumerationAction(Request $request)
    {
        $access_errors = $this->ruleManager->decideAccess("entity", $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not get this resource");
        }

        $conflict_errors = $this->ruleManager->decideConflict("entity", $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not get this resource", $conflict_errors);
        }
        $entities = $this->schemaLoader->loadEntityEnumeration();

        return $this->response->ok($entities);
    }

    /**
     * @Route("property", name="get_property_enumeration", methods={"GET"})
     */
    public function getPropertyEnumerationAction(Request $request)
    {
        $access_errors = $this->ruleManager->decideAccess("property", $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not get this resource");
        }

        $conflict_errors = $this->ruleManager->decideConflict("property", $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not get this resource", $conflict_errors);
        }
        $properties = $this->schemaLoader->loadPropertyEnumeration();
        return $this->response->ok($properties);
    }

    /**
     * @Route("enumeration", name="get_enumeration_enumeration", methods={"GET"})
     */
    public function getEnumerationEnumerationAction(Request $request)
    {
        $access_errors = $this->ruleManager->decideAccess("enumeration", $request);

        if($access_errors > 0)
        {
            return $this->response->forbiddenAccess("You can not get this resource");
        }

        $conflict_errors = $this->ruleManager->decideConflict("enumeration", $request,__DIR__);

        if($conflict_errors > 0)
        {
            return $this->response->conflict("You can not get this resource", $conflict_errors);
        }
        $enumerations = $this->schemaLoader->loadEnumerationEnumeration();
        return $this->response->ok($enumerations);
    }
}
