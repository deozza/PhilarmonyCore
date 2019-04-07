<?php
namespace Deozza\PhilarmonyBundle\Rules;

use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RegexConflictRule
{
    const ERROR_TYPE = "VALUE_NOT_MATCHING_TYPE";

    public function supports($context, Request $request)
    {
        return in_array($request->getMethod(), ['POST', 'PATCH']) && is_a($context, Property::class);

    }

    public function decide($object, Request $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {
        $propertyKind = $schemaLoader->loadPropertyEnumeration($object->getKind());
        $type = $schemaLoader->loadTypeEnumeration($propertyKind['TYPE']);

        try
        {
            preg_match($type['REGEX'], $object->getValue());
        }
        catch(\Exception $e)
        {
            return ["conflict" => self::ERROR_TYPE];
        }
        return ;

    }
}