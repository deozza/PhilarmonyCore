<?php


namespace Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\DatabaseSchema\ValidateProperty;


use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\PropertySchema;
use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\ValidateProperties\ValidateProperty;
use Deozza\PhilarmonyCoreBundle\Tests\testProjectMongodb\tests\DatabaseSchema\DatabaseSchemaTestSetup;
use Symfony\Component\Yaml\Yaml;

class ValidatePropertyTest extends DatabaseSchemaTestSetup
{
    public function testInvalidType()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/invalidType/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');
        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage('Available types are ["string","int","date","float","file","enumeration","entity","embedded"].'."\n".'Invalid type found in property '."'firstname'".'.');
        $propertyValidate->validateType();
    }

    public function testEmbeddedDoesNotExist()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/embeddedDoesNotExist/property.yaml"));
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/embeddedDoesNotExist/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');

        $propertyValidate = new ValidateProperty($property, [], $entitySchema['entities'], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("Entity 'invalid' was not found in the entity config file.\nDeclared in the property 'firstname'.");
        $propertyValidate->validateType();
    }

    public function testEntityDoesNotExist()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityDoesNotExist/property.yaml"));
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityDoesNotExist/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');

        $propertyValidate = new ValidateProperty($property, [], $entitySchema['entities'], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("Entity 'invalid' was not found in the entity config file.\nDeclared in the property 'firstname'.");
        $propertyValidate->validateType();
    }

    public function testEmbeddedIsNotValid()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/embeddedIsNotValid/property.yaml"));
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/embeddedIsNotValid/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['character_naming']['type']);
        $property->setPropertyName('character_naming');

        $propertyValidate = new ValidateProperty($property, [], $entitySchema['entities'], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("Entity character_naming contains a state and can not be an embedded entity.\nDeclared in the property 'character_naming'.");
        $propertyValidate->validateType();
    }

    public function testEntityIsNotValid()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityIsNotValid/property.yaml"));
        $entitySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/entityIsNotValid/entity.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['character_naming']['type']);
        $property->setPropertyName('character_naming');

        $propertyValidate = new ValidateProperty($property, [], $entitySchema['entities'], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("Entity character_naming does not contain a state and can not be an joined entity.\nDeclared in the property 'character_naming'.");
        $propertyValidate->validateType();
    }

    public function testEnumerationDoesNotExist()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/enumerationDoesNotExist/property.yaml"));
        $enumerationSchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/enumerationDoesNotExist/enumeration.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');

        $propertyValidate = new ValidateProperty($property, $enumerationSchema, [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("Enumeration 'invalid' was not found in the enumeration config file.\nDeclared in the property 'firstname'.");
        $propertyValidate->validateType();
    }

    public function testRequiredConstraintIsAbsent()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/requiredConstraintIsAbsent/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');
        $property->setConstraints($propertySchema['properties']['firstname']['constraints']);

        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("A property must contain a required constraint. It was not found in 'firstname'.");
        $propertyValidate->validateConstraints();
    }

    public function testUniqueConstraintIsAbsent()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/uniqueConstraintIsAbsent/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');
        $property->setConstraints($propertySchema['properties']['firstname']['constraints']);

        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("A property must contain a unique constraint. It was not found in 'firstname'.");
        $propertyValidate->validateConstraints();
    }

    public function testUnknownConstraint()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/unknownConstraint/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');
        $property->setConstraints($propertySchema['properties']['firstname']['constraints']);

        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage('Valid property constraints are ["required","unique","default","automatic","lengthMax","lengthMin","mime","gt","lt","gtoe","ltoe","nb","b"]'." Invalid 'unknown' found in property 'firstname'.");
        $propertyValidate->validateConstraints();
    }

    public function testInvalidRangeConstraint()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/invalidRangeConstraint/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['firstname']['type']);
        $property->setPropertyName('firstname');
        $property->setConstraints($propertySchema['properties']['firstname']['constraints']);

        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage("'lengthMax' of property 'firstname' must be lesser than 100.");
        $propertyValidate->validateConstraints();
    }

    public function testMimetypeDoesNotExist()
    {
        $propertySchema = Yaml::parse(file_get_contents(__DIR__ . "/invalid/mimetypeDoesNotExist/property.yaml"));
        $authorizedKeys = Yaml::parse(file_get_contents(__DIR__ . "/../../../../../src/Service/DatabaseSchema/authorizedKeys.yaml"));
        $property = new PropertySchema();
        $property->setType($propertySchema['properties']['file']['type']);
        $property->setPropertyName('file');
        $property->setConstraints($propertySchema['properties']['file']['constraints']);

        $propertyValidate = new ValidateProperty($property, [], [], $authorizedKeys);
        $this->expectException("Exception");
        $this->expectExceptionMessage('Authorized mimetypes are ["text\/csv","image\/gif","image\/jpeg","image\/jpg","application\/json","video\/mpeg","image\/png","application\/pdf","application\/xml"].'." Invalid type 'invalid/mimetype' found in 'file'.");
        $propertyValidate->validateConstraints();
    }


}