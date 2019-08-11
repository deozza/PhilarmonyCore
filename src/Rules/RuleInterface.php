<?php

namespace Deozza\PhilarmonyCoreBundle\Rules;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;

interface RuleInterface
{
    public function supports($entity, $posted, $method) : bool;

    public function decide($entity, $posted, $method, $em, DatabaseSchemaLoader $schemaLoader): ?array;
}