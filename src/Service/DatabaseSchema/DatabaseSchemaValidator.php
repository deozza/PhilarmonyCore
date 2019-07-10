<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema;


class DatabaseSchemaValidator
{
    public function __construct(DatabaseSchemaLoader $schemaLoader)
    {
        $this->schemaLoader = $schemaLoader;
    }

    public function validateEntity()
    {
        $entities = $this->schemaLoader->loadEntityEnumeration();
        var_dump($entities);die;
    }

    public function validateProperty()
    {

    }

    public function validateEnumeration()
    {

    }
}