<?php

namespace Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\Migration;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;

class GenerateMigrationFile
{
    public function __construct(DatabaseSchemaLoader $schemaLoader, string $rootPath)
    {
        $this->schemaLoader = $schemaLoader;
        $this->rootPath = $rootPath;
    }

    public function generate()
    {
        $path = $this->rootPath."src/PhilarmonyMigrations";
        
        $entities = $this->schemaLoader->loadEntityEnumeration();
        $properties = $this->schemaLoader->loadPropertyEnumeration();
        $enumerations = $this->schemaLoader->loadEnumerationEnumeration();
        
        if(!is_dir($path))
        {
            $diffOfEntity      = $this->getDiffOfEntity($entities);
            $diffOfProperty    = $this->getDiffOfProperty($properties);
            $diffOfEnumeration = $this->getDiffOfEnumeration($enumerations);
        }
    }
    
    private function getDiffOfEntity(array $entities, $lastFile = null)
    {
        if($lastFile === null)
        {
            
        }
    }

    private function getDiffOfProperty(array $properties, $lastFile = null)
    {
        if($lastFile === null)
        {

        }
    }

    private function getDiffOfEnumeration(array $enumerations, $lastFile = null)
    {
        if($lastFile === null)
        {

        }
    }
}