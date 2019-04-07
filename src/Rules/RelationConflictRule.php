<?php
namespace Deozza\PhilarmonyBundle\Rules;

use Deozza\PhilarmonyBundle\Entity\EntityJoin;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RelationConflictRule
{
    const ERROR_IS_ONE_TO_ONE = "ENTITY_RELATION_ALREADY_EXISTS";

    public function supports($context, Request $request)
    {
        return in_array($request->getMethod(), ['POST']) && is_a($context, EntityJoin::class);
    }

    public function decide($object,Request $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {

        $kind = $schemaLoader->loadEntityJoinEnumeration($object->getKind())['KIND'];

        if($kind == "ONE_TO_ONE")
        {
            $exists = $em->getRepository(EntityJoin::class)->findOneBy(
                [
                    "kind" => $object->getKind(),
                    "entity1" => $object->getEntity1()
                ]
            );

            if(!empty($exists))
            {
                return ['conflict' => self::ERROR_IS_ONE_TO_ONE];
            }
        }

        return ;
    }
}