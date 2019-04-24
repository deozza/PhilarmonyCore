Property
=

The property is what could be view as a MySQL column, the container of a specific data. By default, it is defined by a type, a `unique` paremeter, a `is_required` parameter and a value.

## Property kind

The Property kind is used to differenciate Properties between them and to tell which type of data is handled by the the Property. It is then unique. All the Property kinds are stored in the yaml file you pointed in the Philarmony configuration (by default, it is `/var/Philarmony/property.json`).

### How to create a new Property kind

In order to create a new property kind, simply add it to your entity `yaml` file configuration.
```yaml
properties:
      title:
            type: string
            required: true
            unique: true

```

|    Property   |                                                Description                                               | Example |
|:-------------:|:--------------------------------------------------------------------------------------------------------:|:-------:|
| property name | The name of you property kind. It must be unique through all your system                                 | title   |
|      type     | The type of data handled by the property. To know more about the types handled, [read this](../TYPES.md) | string  |
|    required   | Defines if the property must be filled (not null)                                                        | true    |
|     unique    | Defines if the value is unique throughout all the system                                                 | true    |
|    default    | If the property submited in the form is null, it takes the default value                                 | 1       |

These are the basic constraints required by Philarmony for a property. All the constraints, advanced ones included, handled by Philarmony are [here](../CONSTRAINTS.md). You also can add new constraints assuming they will be handled by your business logic.

&#9888; ___Once you set the property name, it is not advised to modify it later. You will need to ensure the migration in your database to stay relevant with your configuration___

### Property routes

| Method |                  Route                  |               Description              |                Details                |
|:------:|:---------------------------------------:|:--------------------------------------:|:-------------------------------------:|
|  POST  | /api/{entity_name}/{id]/{property_name} | Add a property to the given entity     | [More](#post-a-property)              |
|   GET  | /api/{entity_name}/{id]/{property_name} | Get the property of the given entity   | [More](#get-a-property-of-an-entity)) |
|  PATCH | /api/{entity_name}/{id]/{property_name} | Patch the property of the given entity | [More](#edit-a-property-of-an-entity) |
| DELETE | /api/{entity_name}/{id]/{property_name} | Remove a property of the given entity  | [More](#remove-a-property)            |

#### Post a property

`POST /api/{entity_name}/{id}/{property_name}`

| QUERY property |  Type  |          Description          |
|:--------------:|:------:|:-----------------------------:|
| entity_name    | string | The name of the Entity kind   |
| id             | uuid   | The id of the entity          |
| property_name  | string | The name of the property kind |

_The data inside your request must match the fields listed in the `post.properties` of the entity and their configuration in the dedicated `yaml` file._

__Request example :__

```
curl --header "Content-Type: application/json"
     --request POST
     --data '{"description": "Animals are allowed"}'
     http://www.mysuper.app/api/entity/offer/00100000-0000-4000-a000-000000000000/description
```

__Response example :__

```json
 {
   "uuid": "00100000-0000-4000-a000-000000000000",
   "kind": "offer",
   "owner": "00100000-0000-4000-a000-000000000000",
   "date_of_Creation": "2019-01-01T12:00:00+02:00",
   "properties": {
     "title": "Stunning appartment in Tokyo",
     "description": [
        "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
        "Animals are allowed"
        ],
     "price": 82,
     "house_category": "appartment"
     }
 }
```

#### Get a property of an entity

`GET /api/{entity_name}/{id}/{property_name}`

| QUERY property |  Type  |          Description          |
|:--------------:|:------:|:-----------------------------:|
| entity_name    | string | The name of the Entity kind   |
| id             | uuid   | The id of the entity          |
| property_name  | string | The name of the property kind |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/offer/00100000-0000-4000-a000-000000000000/title
```

__Response example :__

```json
   "Stunning appartment in Tokyo"
```

#### Edit a property of an entity

`PATCH /api/{entity_name}/{id}/{property_name}`

| QUERY property |  Type  |          Description          |
|:--------------:|:------:|:-----------------------------:|
| entity_name    | string | The name of the Entity kind   |
| id             | uuid   | The id of the entity          |
| property_name  | string | The name of the property kind |

_The data inside your request must match the fields listed in the `post.properties` of the entity and their configuration in the dedicated `yaml` file._

__Request example :__

```
curl --header "Content-Type: application/json"
     --request POST
     --data '{"title": "The greatest appartment in Tokyo"}'
     http://www.mysuper.app/api/entity/offer/00100000-0000-4000-a000-000000000000/description
```

__Response example :__

```json
 {
   "uuid": "00100000-0000-4000-a000-000000000000",
   "kind": "offer",
   "owner": "00100000-0000-4000-a000-000000000000",
   "date_of_Creation": "2019-01-01T12:00:00+02:00",
   "properties": {
     "title": "The greatest appartment in Tokyo",
     "description": [
        "Located in central Tokyo. 2 bedrooms, bathroom, wifi."
        ],
     "price": 82,
     "house_category": "appartment"
     }
 }
```


#### Remove a property

`DELETE /api/{entity_name}/{id}/{property_name}`

| QUERY property |  Type  |          Description          |
|:--------------:|:------:|:-----------------------------:|
| entity_name    | string | The name of the Entity kind   |
| id             | uuid   | The id of the entity          |
| property_name  | string | The name of the property kind |

_By default, the controller removes all the specified property. To remove only one key of a property, add a `key` url parameter with the key of the element you want to remove`_

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/offer/00100000-0000-4000-a000-000000000000/description
```

__Response example :__

As the HTTP response code is 204, there is no body inside the response.