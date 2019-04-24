Types
=

|     Type    |                           Description                           |
|:-----------:|:---------------------------------------------------------------:|
| string      | A sequence of characters                                        |
| int         | A sequence of natural numbers                                   |
| date        | A date at the format yyyy-MM-dd                                 |
| price       | A float adapted to handle money values                          |
| file        | Binary content of a file                                        |
| enumeration | A list of possible values                                       |
| entity      | Link the property to an existent entity (as a join) by its uuid |
| embedded    | The property contains all the property of the given entity      |
| array       | An array collection                                             |

## Advanced types

### Enumeration

It must be chained to an enumeration name defined in the yaml file dedicated. 

__Example : __

```yaml
properties:
    annonce_category:
        type: enumeration.annonce_category
        required: true
        unique: false
```

### Entity

It must be chained to an entity name defined in the yaml file dedicated.

__Example : __

```yaml
properties:
    annonce:
        type: entity.annonce
        required: true
        unique: false
```

### Embedded

It must be chained to an entity name defined in the yaml file dedicated.

__Example : __

```yaml
properties:
    message:
        type: embedded.message
        required: true
        unique: false
```

### Array

It must be linked to another type. 

__Examples : __

```yaml
properties:
    photo:
        type: array.file
        required: false
        unique: false
    message:
        type: array.embedded.message
        required: true
        unique: false
```