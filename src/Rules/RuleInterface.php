<?php

namespace Deozza\PhilarmonyCoreBundle\Rules;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

interface RuleInterface
{
    public function supports($entity, $posted, $method) : bool;

    public function decide($entity, $posted, $method, DocumentManager $dm, DatabaseSchemaLoader $schemaLoader): ?array;
}