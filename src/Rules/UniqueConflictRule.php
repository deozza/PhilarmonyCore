<?php
namespace Deozza\PhilarmonyBundle\Rules;

use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class UniqueConflictRule
{
    const ERROR_EXISTS = "PROPERTY_ALREADY_EXISTS";

    public function supports($context, $method)
    {
        return false; //in_array($method, ['POST']) && is_a($context, Property::class);
    }

    public function decide($object, Request $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {
        $propertyKind = $schemaLoader->loadPropertyEnumeration($object->getKind());


        if($propertyKind['unique'] == true)
        {
            $alreadyExists = $em->getRepository(Property::class)->findOneBy(
                [
                    "kind" => $object->getKind(),
                    "value" => $object->getValue()
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