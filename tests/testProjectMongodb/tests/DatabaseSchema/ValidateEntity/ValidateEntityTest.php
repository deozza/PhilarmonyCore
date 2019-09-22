<?php


namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\DatabaseSchema\ValidateEntity;


use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity\EntitySchema;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateEntity\ValidateEntity;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\DatabaseSchema\DatabaseSchemaTestSetup;
use Symfony\Component\Yaml\Yaml;

class ValidateEntityTest extends DatabaseSchemaTestSetup
{
    public function testEmptyProperty()
    {
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[]);
        $this->expectException("Exception");
        $this->expectExceptionMessage("character_naming does not contain properties.");
        $validateEntity->validateProperties();
    }

    public function testUnknownProperty()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/unknownProperty/entity.yaml"));
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/valid/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setProperties($entitySchema['entities']['character_naming']['properties']);
        $validateEntity = new ValidateEntity($entity, $propertySchema, $authorizedKeys,$entitySchema );
        $this->expectException("Exception");
        $this->expectExceptionMessage("Property unknown does not exist.\nDeclared in character_naming");
        $validateEntity->validateProperties();
    }


}