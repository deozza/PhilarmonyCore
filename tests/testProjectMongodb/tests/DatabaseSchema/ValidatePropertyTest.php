<?php


namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\DatabaseSchema;


use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\PropertySchema;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\ValidateProperty;
use Symfony\Component\Yaml\Yaml;

class ValidatePropertyTest extends DatabaseSchemaTestSetup
{
    public function testInvalidType()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__."/invalid/propertyInvalidType/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__."/../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $propertyValidate->validateType();
    }
}