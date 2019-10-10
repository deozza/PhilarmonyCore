<?php
namespace Deozza\PhilarmonyCoreBundle\Rules;

use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class UniqueConflictRule implements RuleInterface
{
    const ERROR_EXISTS = "PROPERTY_ALREADY_EXISTS";

    public function supports($entity, $posted, $method): bool
    {
        return in_array($method, ['POST', 'PATCH']);
    }

    public function decide($entity, $posted, $method,  DocumentManager $dm, DatabaseSchemaLoader $schemaLoader): ?array
    {
        if(empty($posted)) return null;
        $kind = $entity->getKind();
        $properties = $this->getProperties($schemaLoader, $kind);
        $properties = $this->onlyUniqueProperties($properties);

        foreach($properties as $key=>$value)
        {
            if(array_key_exists($key, $posted))
            {
                $exist = $dm->getRepository(Entity::class)->findAllFiltered(['equal.properties.data.'.$key=>$posted[$key]], [], $entity->getKind())->getQuery()->execute()->toArray();
                if(count($exist)> 0)
                {
                    return ["conflict" => [$key=>self::ERROR_EXISTS]];
                }
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
                    $properties[$item] = $schemaLoader->loadPropertyEnumeration($item);
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