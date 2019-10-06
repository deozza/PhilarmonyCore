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
        $validateEntity = new ValidateEntity($entity, $propertySchema['properties'], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("Property unknown does not exist in the properties schema.\nDeclared in character_naming");
        $validateEntity->validateProperties();
    }

    public function testEntityStateDoesNotContainDefault()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityStateDoesNotContainDefault/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage('__default state missing in character_naming');
        $validateEntity->validateStates();
    }

    public function testEntityStateWithoutMethod()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityStateWithoutMethod/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("A state must contain at least one method.\nDeclared in the state '__default' of the entity 'character_naming");
        $validateEntity->validateStates();
    }

    public function testUnexpectedMethod()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/unexpectedMethod/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("OPTION method does not exist.\nDeclared in the entity 'character_naming'.");
        $validateEntity->validateStates();
    }

    public function testEntityStateMethodWithoutBy()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityStateMethodWithoutBy/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $entity->setProperties($entitySchema['entities']['character_naming']['properties']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("You must define who is able to execute a method. 'by' key was not declared for the method POST of the entity 'character_naming'.");
        $validateEntity->validateStates();
    }

    public function testEntityStateMethodByInvalidString()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityStateMethodInvalidBy/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $entity->setProperties($entitySchema['entities']['character_naming']['properties']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("'by' must be an array. It was not declared as such in the entity 'character_naming'.");
        $validateEntity->validateStates();
    }

    public function testEntityStateMethodByInvalidKey()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityStateMethodInvalidByKey/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $entity->setProperties($entitySchema['entities']['character_naming']['properties']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("'by' expected to be ".'["roles","users"]'.". Invalid value 'foo' found in 'character_naming'.");
        $validateEntity->validateStates();
    }
    public function testEntityStatePOSTWithInvalidProperties()
    {
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityStatePostInvalidProperties/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $entity = new EntitySchema();
        $entity->setEntityName("character_naming");
        $entity->setStates($entitySchema['entities']['character_naming']['states']);
        $entity->setProperties($entitySchema['entities']['character_naming']['properties']);
        $validateEntity = new ValidateEntity($entity, [], $authorizedKeys,[] );
        $this->expectException("Exception");
        $this->expectExceptionMessage("'foo' does not exist in the entity 'character_naming'. Was declared in one of its state.");
        $validateEntity->validateStates();
    }
}