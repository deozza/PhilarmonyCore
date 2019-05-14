Entity
=
An Entity is what could be view as a MySQL table, a main container of data. By default, it is defined by an owner (the User who created the entity), a kind, the date it was create and its properties.

## Entity kind

The Entity kind is used to differentiate Entities between them. An entity is defined by it name, its properties and the validation states it could have (more about the validation [here](../../Validaton/VALIDATIONSTATE.md) ). An entity kind is unique thoughout all the API. All the Entity kinds are stored in the yaml file you pointed in the Philarmony configuration (by default, it is `/var/Philarmony/entity.json`).

### How to create a new Entity kind

In order to create a new entity kind, simply add it to your entity `yaml` file configuration.
```yaml
entities:
    offer:
        properties: ['title', 'description', 'price', 'annonce_category', 'photo']
        states:
            __default:
                methods:
                    POST:
                        properties: [title, description, price, annonce_category, nbPersonMax]
                        by:
                          roles: [ROLE_USER]
```

|                       Property                       |                                                         Description                                                         |                            Example                           |
|:----------------------------------------------------:|:---------------------------------------------------------------------------------------------------------------------------:|:------------------------------------------------------------:|
| entity name                                          | The name of you entity kind. It must be unique through all your system                                                      | offer                                                        |
| properties                                           | The list of the properties the entity is carrying. They must be defined in the yaml config file dedicated to the properties | ['title', 'description', 'price', 'place_category', 'photo'] |
| states.{state_name}                                  | The list of all possible states of the entity                                                                               | __default                                                    |
| states.{state_name}.methods.{method_name}            | The methods available in that states                                                                                        | POST                                                         |
| states.{state_name}.methods.{method_name}.properties | Which properties are handled by the method                                                                                  | ['title', 'description', 'price', 'place_category']          |
| states.{state_name}.methods.{method_name}.by         | Which users or group of user is able to use that method                                                                     | roles:[ROLE_USER]                                            |


&#9888; ___Once you set the entity name, it is not advised to modify it later. You will need to ensure the migration in your database to stay relevant with your configuration___


### Entity routes

| Method |              Route             |                  Description                  |                 Details                |
|:------:|:------------------------------:|:---------------------------------------------:|:--------------------------------------:|
|  POST  | /api/entity/{entity_name}      | Post a new entity with the specified kind     | [More](#post-an-entity)                |
|   GET  | /api/entity/{entity_name}      | Get all the entries of a specific entity kind | [More](#get-all-the-entries-of-a-kind) |
|   GET  | /api/entity/{entity_name}/{id} | Get a specific entity                         | [More](#get-a-specific-entity)         |
|  PATCH | /api/entity/{entity_name}/{id} | Edit an entity                                | [More](#edit-an-entity)                |
| DELETE | /api/entity/{entity_name}/{id} | Delete an Entity                              | [More](#delete-an-entity)              |

#### Post an entity

`POST /api/entity/{entity_name}`

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|   entity_name  | string | The name of the Entity kind |

_The data inside your request must match the fields listed in the `post.properties` of the entity and their configuration in the dedicated `yaml` file._

__Request example :__

```
curl --header "Content-Type: application/json"
     --request POST
     --data '{"title": "Stunning appartment in Tokyo", "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.", "price": 82, "place_category":"appartment"}'
     http://www.mysuper.app/api/entity/offer
```

__Response example :__

```json
{
  "uuid": "00100000-0000-4000-a000-000000000000",
  "kind": "offer",
  "owner": "00100000-0000-4000-a000-000000000000",
  "date_of_Creation": "2019-01-01T12:00:00+02:00",
  "validationState": "__default",
  "properties": {
    "title": "Stunning appartment in Tokyo",
    "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
    "price": 82,
    "place_category": "appartment"
  }
}
```

#### Get all the entries of a kind

`GET /api/entity/{entity_name}`

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|   entity_name  | string | The name of the Entity kind |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/offer
```

__Response example :__

```json
[
    {
      "uuid": "00100000-0000-4000-a000-000000000000",
      "kind": "offer",
      "owner": "00100000-0000-4000-a000-000000000000",
      "date_of_Creation": "2019-01-01T12:00:00+02:00",
      "validationState": "__default",
      "properties": {
        "title": "Stunning appartment in Tokyo",
        "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
        "price": 82,
        "place_category": "appartment"
      }
    },
    {
      "uuid": "00100000-0000-5000-a000-000000000000",
      "kind": "offer",
      "owner": "00100000-0000-4000-a000-000000000000",
      "date_of_Creation": "2019-01-02T12:00:00+02:00",
      "validationState": "__default",
      "properties": {
        "title": "Villa in Corsica",
        "description": "Near the beach.",
        "price": 150,
        "place_category": "House"
      }
    }
]
```

You can filter the results of the request. To do so, add a `filterBy` url query parameter with the kind of filter operator and the name of the property you want to filter by. If the property is within the json properties, prefix it with `properties`.

Here is the list of the possible operators :

* equal
* like
* lesser
* lesserOrEqual
* greater
* greaterOrEqual
 
__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/offer?filterBy[equal.properties.price]=150
```

__Response example :__

```json
[
    {
      "uuid": "00100000-0000-5000-a000-000000000000",
      "kind": "offer",
      "owner": "00100000-0000-4000-a000-000000000000",
      "date_of_Creation": "2019-01-02T12:00:00+02:00",
      "validationState": "__default",
      "properties": {
        "title": "Villa in Corsica",
        "description": "Near the beach.",
        "price": 150,
        "place_category": "House"
      }
    }
]
```

You can filter the results of the request. To do so, add a `sortBy` url query parameter with the name of the property you want to sort by. If the property is within the json properties, prefix it with `properties`. The sort value must be either `ASC` or `DESC`.

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/offer?sortBy[properties.price]=DESC
```

__Response example :__

```json
[
    {
      "uuid": "00100000-0000-5000-a000-000000000000",
      "kind": "offer",
      "owner": "00100000-0000-4000-a000-000000000000",
      "date_of_Creation": "2019-01-02T12:00:00+02:00",
      "validationState": "__default",
      "properties": {
        "title": "Villa in Corsica",
        "description": "Near the beach.",
        "price": 150,
        "place_category": "House"
      }
    },
    {
      "uuid": "00100000-0000-4000-a000-000000000000",
      "kind": "offer",
      "owner": "00100000-0000-4000-a000-000000000000",
      "date_of_Creation": "2019-01-01T12:00:00+02:00",
      "validationState": "__default",
      "properties": {
        "title": "Stunning appartment in Tokyo",
        "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
        "price": 82,
        "place_category": "appartment"
      }
    }
]
```

#### Get a specific entity
`GET /api/entity/{id}`

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|       id       |  uuid  | The uuid of the Entity      |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/00100000-0000-4000-a000-000000000000
```

__Response example :__

```json
{
  "uuid": "00100000-0000-4000-a000-000000000000",
  "kind": "offer",
  "owner": "00100000-0000-4000-a000-000000000000",
  "date_of_Creation": "2019-01-01T12:00:00+02:00",
  "validationState": "__default",
  "properties": {
    "title": "Stunning appartment in Tokyo",
    "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
    "price": 82,
    "place_category": "appartment"
  }
}
```

#### Edit an entity
`PATCH /api/entity/{id}`

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|       id       |  uuid  | The uuid of the Entity      |

_The data inside your request must match the fields listed in the `patch.properties` of the entity and their configuration in the dedicated `yaml` file._


__Request example :__

```
curl --header "Content-Type: application/json"
     --request PATCH
     --data '{"title": "The best appartment in Tokyo"}'
     http://www.mysuper.app/api/entity/00100000-0000-4000-a000-000000000000
```

__Response example :__

```json
{
  "uuid": "00100000-0000-4000-a000-000000000000",
  "kind": "offer",
  "owner": "00100000-0000-4000-a000-000000000000",
  "date_of_Creation": "2019-01-01T12:00:00+02:00",
  "validationState": "__default",
  "properties": {
    "title": "The best appartment in Tokyo",
    "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
    "price": 82,
    "place_category": "appartment"
  }
}
```

#### Delete an entity

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|       id       |  uuid  | The uuid of the Entity      |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request DELETE
     http://www.mysuper.app/api/entity/00100000-0000-4000-a000-000000000000
```

__Response example :__

As the HTTP response code is 204, there is no body inside the response.

### Launch post scripts

You may need to execute scripts at the end of a sucessful request. For example, if you need to modify data of an entity according to a new entity freshly posted. Or if you need to send an email after a successful request. To do so, add a `post_scrips` node below an entity state:

```yaml
entities:
  conversation:
    properties:
      - message
      - participants
    states:
      __default:
        methods:
          POST:
            properties:
              - message
            by:
              roles:
                - ROLE_USER
            post_scripts:
              - addParticipants
```

In this example, Philarmony will dispatch an event called `addParticipants`. All you need to do, is to subscribe to this event in your app. To know more about it, read the [Symfony documentation](https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber).