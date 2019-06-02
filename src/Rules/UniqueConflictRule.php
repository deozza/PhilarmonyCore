<?php
namespace Deozza\PhilarmonyCoreBundle\Rules;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;

class UniqueConflictRule implements RuleInterface
{
    const ERROR_EXISTS = "PROPERTY_ALREADY_EXISTS";

    public function supports($entity, $posted, $method): bool
    {
        return in_array($method, ['POST', 'PATCH']);
    }

    public function decide($entity, $posted, $method, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader): ?array
    {
        $kind = $entity->getKind();
        $properties = $this->getProperties($schemaLoader, $kind);
        $properties = $this->onlyUniqueProperties($properties);

        $submited = $entity->getProperties();
        foreach($properties as $key=>$value)
        {
            $exist = $em->getRepository(Entity::class)->findAllFiltered(['equal.properties.'.$key=>$submited[$key]], [], $entity->getKind());
            if(count($exist)> 0 && in_array($key, $posted))
            {
                return ["conflict" => [$key=>self::ERROR_EXISTS]];
            }
        }
        return null;
    }

    private function getProperties($schemaLoader, $kind)
    {
        $propertiesConfig = $schemaLoader->loadEntityEnumeration($kind)['properties'];

        $properties = [];
        foreach($propertiesConfig as $property)
        {
            $properties[$property] = $schemaLoader->loadPropertyEnumeration($property);
            $type = explode('.', $properties[$property]['type']);
            if($type[0] === "embedded")
            {
                unset($properties[$property]);
                $sub = $schemaLoader->loadEntityEnumeration($type[1])['properties'];
                foreach($sub as $item)
                {
                    $properties[$property.'.'.$item] = $schemaLoader->loadPropertyEnumeration($item);
                }
            }
        }

        return $properties;
    }

    private function onlyUniqueProperties(array $properties)
    {
        foreach($properties as $key=>$value)
        {
            if($value['constraints']['unique'] !== true)
            {
                unset($properties[$key]);
            }
        }

        return $properties;

    }
}