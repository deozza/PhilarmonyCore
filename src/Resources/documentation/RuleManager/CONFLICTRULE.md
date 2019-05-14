# Conflict rules

A conflict rules is a custom and advanced constraint that need to be developed and cannot be added to a configuration file.

&#9888; ___Conflict rules are applied before flushing the database. Therefore, if one of the rules is not respected, the entity will not be persisted and you would get a 409 response.___

## Add a new conflict rule

To add a new conflict rule, create a folder in your app that ends with `Rules`. It can be stored everywhere in your `src` folder. Then, create a class with `ConflictRule` at the end. That class must implement the `RuleInterface` class.

```php
<?php
namespace App\path\to\your\ApplicationRules;

use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Deozza\PhilarmonyBundle\Rules\RuleInterface;

class FooConflictRule implements RuleInterface
{
    public function supports($entity, $method)
    {
        
    }

    public function decide($entity, $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {

    }

}
```

### supports function

The `supports` function is here to let Philarmony knows in which scenario the rule is used. It must returns a boolean.

__Example: __

```php
<?php
class FooConflictRule implements RuleInterface
{
//
    public function supports($entity, $method)
    {
        return in_array($method, ['POST', 'PATCH']);
    }
    
//
}
```


### decide function

The `decide`function is used to let Philarmony knows if the entity passed contains the correct data. This is where you put your business logic. 

__Example: __

```php
<?php
class FooConflictRule implements RuleInterface
{
    const ERROR = "There is an error";
//
    public function decide($entity, $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {
        $properties = $entity->getProperties();
        
        if(empty($properties))
        {
            return ["conflict"=>[$entity->getUuid() => self::ERROR]];
        }
        
        return ;
    }
    
//
}
```