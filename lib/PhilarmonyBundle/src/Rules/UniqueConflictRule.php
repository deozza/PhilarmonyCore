<?php
namespace Deozza\PhilarmonyBundle\Rules;

use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UniqueConflictRule
{
    const ERROR_EXISTS = "PROPERTY_ALREADY_EXISTS";

    public function supports($context, Request $request)
    {
        return in_array($request->getMethod(), ['POST']);
    }

    public function decide($object, Request $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {
        $propertyKind = $schemaLoader->loadPropertyEnumeration($object->getKind());


        if($propertyKind['UNIQUE'] == true)
        {
            $alreadyExists = $em->getRepository(Property::class)->findOneBy(
                [
                    "entity" => $object->getEntity()->getId(),
                    "kind" => $object->getKind()
                ]
            );

            if(!empty($alreadyExists))
            {
                return ["conflict" => self::ERROR_EXISTS];

            }
        }

        return ;

    }
}