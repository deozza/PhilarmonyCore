Constraints
=

Validation state are incremental. In that way, more the entity reaches the last state, more it has privileges and possible actions. In order to pass from one state to the next, the entity and its data must be valid according to constraints. All the constraints are listed below teh `constraints` tag :

```yaml
entities:
  offer:
    states:
        # [...]

      public:
        constraints:
      # [...]
``` 

###Manual validation

Manual validation is useful when the entity needs human approval to pass to the next state. To enable a manual validation, add a `manual` tag like in the following example:

```yaml
entities;
  offer:
    states:
      __default:
      # [...]
      public:
        constraints:
          manual:
            by:
              roles: [ROLE_ADMIN]
```

The users allowed in the `by` tag will have to send a `POST` request to `/api/validation/{id}` to manually validate the entity.

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|       id       |  uuid  | The uuid of the Entity      |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request POST
     http://www.mysuper.app/api/validate/00100000-0000-4000-a000-000000000000
```

__Response example :__

```json
{
  "uuid": "00100000-0000-4000-a000-000000000000",
  "kind": "offer",
  "owner": "00100000-0000-4000-a000-000000000000",
  "date_of_Creation": "2019-01-01T12:00:00+02:00",
  "validationState": "validated",
  "properties": {
    "title": "Stunning appartment in Tokyo",
    "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
    "price": 82,
    "place_category": "appartment"
  }
}
```

### Automatic validation

Each time an entity is posted or patched, the validation process is launched in order to pass to the next validation state. It will parse the constraints you listed and try to automatically assert all the non-manual ones. 

Constraints are linked to a property of the entity. A property can have multiple constraints. Here is an example of the constraints required to pass a state:

```yaml
constraints:
  properties.date_begin:
    - notBetween.reservation(date_begin,date_end)
  properties.date_end:
    - greaterThan.self(#date_begin)
    - notBetween.reservation(date_begin,date_end)
  properties.nbPerson:
    - lesserThanOrEqual.self(#annonce.nbPersonMax)
  properties.annonce.validationState:
    - equal(public)
``` 

## Possible constraints

|        Name        |                                Description                               |                    Example                   |
|:------------------:|:------------------------------------------------------------------------:|:--------------------------------------------:|
| manual             | Set a manual validation to pass the state                                |                                              |
| greaterThan        | The value submitted must be greater than the value specified             | greaterThan.self(#date_begin)                |
| greaterThanOrEqual | The value submitted must be greater than or equal to the value specified | greaterThanOrEqual.self(#date_begin)         |
| lesserThan         | The value submitted must be lesser than the value specified              | lesserThan.self(#annonce.nbPersonMax)        |
| lesserThanOrEqual  | The value submitted must be lesser than or equal to the value specified  | lesserThanOrEqual.self(#annonce.nbPersonMax) |
| equal              | The value submitted must be equal to the value specified                 | equal(public)                                |
| between            | The value submitted must be between the values specified                 | between.reservation(date_begin,date_end)     |
| notBetween         | The value submitted must not be between the values specified             | notBetween.reservation(date_begin,date_end)  |

In the case you need to compare the submitted value with another property of the entity, add `self` to the constraint.

__Example :__

```yaml
properties.date_end:
  - greaterThan.self(#date_begin)
```

In the case you need to compare the submitted value with a property of another entity, add the entity kind to the constraint.


__Example :__

```yaml
properties.date_begin:
  - notBetween.reservation(date_begin,date_end)
          
```

Except for `between` and `notBetween` constraints, if the value specified is the value is dynamic, you need to prefix it with `#`. 