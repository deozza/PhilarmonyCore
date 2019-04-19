Entity
=

An Entity is what could be view as a MySQL table, a main container of data. By default, it is defined by an owner (the User who created the entity), a kind and its properties.

## Entity kind

The Entity kind is used to differenciate Entities between them and to tell which properties are handled by the Entity. It is then unique. All the Entity kinds are stored in the json file you pointed in the Philarmony configuration (by default, it is `/var/Philarmony/entity.json`).

### How to create a new Entity kind



### Entity kind enumeration

To get the list of all the Entity kinds your project is using, send a `GET` request to `/api/databaseSchema/entity` :

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/databaseSchema/entity
```

__Response example :__

```json
{
    "CHARACTER": ["NAME", "AGE", "RACE"],
    "WEAPON": ["NAME", "EFFECT", "PRICE"] 
}
```

### Entity routes

| Method |            Route           |                Description                |             Details            |
|:------:|:--------------------------:|:-----------------------------------------:|:------------------------------:|
|  POST  | /entity/{entity_name}      | Post a new entity of the specific kind    | [More](#post-an-entity)        |
|   GET  | /entity/{entitu_name}      | Get all the entities of the specific kind | [More](#get-all-the-entities)  |
|   GET  | /entity/{entity_name}/{id} | Get a specific entity                     | [More](#get-a-specific-entity) |
| DELETE | /entity/{entity_name}/{id} | Delete an Entity                          | [More](#delete-an-entity)      |

#### Post an Entity

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|   entity_name  | string | The name of the Entity kind |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request POST
     http://www.mysuper.app/api/entity/character
```

__Response example :__

```json
{
    
}
```

#### Get all the Entity

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|   entity_name  | string | The name of the Entity kind |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/character
```

__Response example :__

```json
{
    
}
```

#### Get a specific Entity

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|   entity_name  | string | The name of the Entity kind |
|       id       |  uuid  | The uuid of the Entity      |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request GET
     http://www.mysuper.app/api/entity/character/00100000-0000-4000-a000-000000000000
```

__Response example :__

```json
{
    
}
```

#### Delete an Entity

| QUERY property |  Type  |         Description         |
|:--------------:|:------:|:---------------------------:|
|   entity_name  | string | The name of the Entity kind |
|       id       |  uuid  | The uuid of the Entity      |

__Request example :__

```
curl --header "Content-Type: application/json"
     --request DELETE
     http://www.mysuper.app/api/entity/character/00100000-0000-4000-a000-000000000000
```

__Response example :__

As the HTTP response code is 204, there is no body inside the response.