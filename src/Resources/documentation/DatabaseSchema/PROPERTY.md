Property
=

The property is what could be view as a MySQL column, the container of a specific data. By default, it is defined by a type, a `unique` paremeter, a `is_required` parameter and a value.

## Property kind

The Property kind is used to differenciate Properties between them and to tell which type of data is handled by the the Property. It is then unique. All the Property kinds are stored in the json file you pointed in the Philarmony configuration (by default, it is `/var/Philarmony/property.json`).

### How to create a new Property kind

#### Via API (recommended) | `POST /api/databaseSchema/property`

The recommended way to add a new Property kind is by using the dedicated API route :

| JSON property |  Type  |                  Description                  |
|:-------------:|:------:|:---------------------------------------------:|
| name          | string | The name of the Property kind                 |
| properties    | array  | All the property kinds the Entity will handle |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request POST
     --data '{"name": "Character", "properties" :"['NAME', 'AGE', 'RACE']"}'
     http://www.mysuper.app/api/databaseSchema/entity
``` 
__Response example :__

```json
{
    "CHARACTER": ["NAME", "AGE", "RACE"]
}
```
#### Manually

### Property kind enumeration

### Property routes