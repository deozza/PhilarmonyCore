Entity
=
An Entity is what could be view as a MySQL table, a main container of data. By default, it is defined by an owner (the User who created the entity), a kind, the date it was create and its properties.

## Entity kind

The Entity kind is used to differenciate Entities between them and to tell which properties are handled by the Entity. It is then unique. All the Entity kinds are stored in the json file you pointed in the Philarmony configuration (by default, it is `/var/Philarmony/entity.json`).

### How to create a new Entity kind

In order to create a new entity kind, simply add it to your entity `yaml` file configuration.
```yaml
entities:
     offer:
            properties: ['title', 'description', 'price', 'annonce_category', 'photo']
            visible: ['all']
            post:
                  properties: 'all'
                  by: ['owner', 'admin']
            patch:
                  properties: 'all'
                  by : ['owner', 'admin']
```

|     Property     |                                                         Description                                                         |                       Example                       |
|:----------------:|:---------------------------------------------------------------------------------------------------------------------------:|:---------------------------------------------------:|
|    entity name   | The name of you entity kind. It must be unique through all your system                                                      | offer                                               |
|    properties    | The list of the properties the entity is carrying. They must be defined in the yaml config file dedicated to the properties | ['title', 'description', 'price', 'place_category'] |
|      visible     | Which user has access to the entity                                                                                         | ['all']                                             |
|  post.properties | Which properties are meant to be in the post form                                                                           | 'all'                                               |
|      post.by     | Who is able to create an entity or add properties to it                                                                     | ['owner', 'admin']                                  |
| patch.properties | Which properties are meant to be in the patch form                                                                          | 'all'                                               |
|     patch.by     | Who is able to edit an entity                                                                                               | ['owner', 'admin']                                  |

The values of `visible`, `post.by` and `patch.by` are define by your business logic.

Also, you can set other constraints to an entity assuming it will be handled by your business logic.

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

_You can filter the results of the request by the content of the properties. To do that add`filter[]` url query parameters with the name of the property you want to filter with._

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
      "properties": {
        "title": "Villa in Corsica",
        "description": "Near the beach.",
        "price": 150,
        "place_category": "House"
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