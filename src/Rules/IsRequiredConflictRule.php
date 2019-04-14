<?php
namespace Deozza\PhilarmonyBundle\Rules;

use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class IsRequiredConflictRule
{
    const ERROR_IS_REQUIRED = "PROPERTY_IS_REQUIRED";

    public function supports($context, $method)
    {
        return in_array($method, ['DELETE']) && is_a($context, Property::class);
    }

    public function decide($object,Request $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {
        $propertyKind = $schemaLoader->loadPropertyEnumeration($object->getKind());


        if($propertyKind['required'] == true)
        {
            return ["conflict" => self::ERROR_IS_REQUIRED];
        }

        return ;

    }
}