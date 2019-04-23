Philarmony
=

## Table of contents

 * [About](#About)
 * [Installation](#Installation)
 * [Configuration](#Configuration)
 * [Example of usage](#Example-of-usage)
 * [How it works](#How-it-works)

## About

Philarmony is a bundle made to help you create a modular REST API. From database to controllers and forms, without forgetting authorization and data validation, manage your API easily in minutes.

## Installation

You can install using composer, assuming it is already installed globally :

`composer require deozza\philarmony-bundle`

## Configuration

In order yo use Philarmony in your project, you need to configure it. First, create a `philarmony.yaml` file with the following structure : 

```yaml
deozza_philarmony:
    directory:
      entity: ~
      property: ~
      enumeration: ~
```

This will be used to locate the database schema files. By default they are created and stored in `/var/Philarmony/`. 

Then, to use the embedded services, as the controllers and the repositories, of Philarmony, you need to enable them in your `/config/services.yaml` and in your `config/routes/annotations.yaml` :
```yaml
services: 
    Deozza\PhilarmonyBundle\Controller:
      resource: '@DeozzaPhilarmonyBundle\Controller'
      tags: ['controller.service_arguments']
      
    Deozza\PhilarmonyBundle\Repository:
      resource: '@DeozzaPhilarmonyBundle\Repository'
      tags: ['doctrine.service_entity']  
```

```yaml
philarmony_controllers:
    resource: '@DeozzaPhilarmonyBundle\Controller'
    type: annotation
    prefix: /
```

## Example of usage

For this example, our API example will manage vacation rentals. Users are able to create rental offers, book vacations and communicate through an internal message service.

Therefore, the database would be constituted with 4 different entity kinds : 
* `offer` : contains all the infos about a rental offer
* `booking` : contains all the info about a booking and the offer it is linked with 
* `conversation`: contains all the message of a conversation between 2 users
* `message` : contains the content of the message and other informations about it

These entities are defined by default inside the `entity.yaml` configuration file, and their properties inside the `property.yaml` file. 

Here is an example of a cUrl request with its body (in JSON) to create a new rental offer : 

```
curl --header "Content-Type: application/json"
     --request POST
     --data '{"title": "Stunning appartment in Tokyo", "description": "Located in central Tokyo. 2 bedrooms, bathroom, wifi.", "price": 82, "place_category":"appartment"}'
     http://www.mysuper.app/api/entity/offer
 ```
 
As you can see, the kind of entity you post is specified in the url. This will allow Philarmony to adapt the comportment of the API following the logic you implemented in the `yaml` configuration files and via your business logic. Here is the JSON response you would expect from the previous request:

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

Say that an offer can have multiple descriptions and you want to add another one, simply send a request like the following : 

```
curl --header "Content-Type: application/json"
     --request POST
     --data '{"description": "Animals are allowed"}'
     http://www.mysuper.app/api/offer/00100000-0000-4000-a000-000000000000/description
 ```
 Here will be the expected response :
 
 ```json
 {
   "uuid": "00100000-0000-4000-a000-000000000000",
   "kind": "offer",
   "owner": "00100000-0000-4000-a000-000000000000",
   "date_of_Creation": "2019-01-01T12:00:00+02:00",
   "properties": {
     "title": "Stunning appartment in Tokyo",
     "description": {
        "1":"Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
        "2":"Animals are allowed"
        },
     "price": 82,
     "house_category": "appartment"
     }
 }
 ```
 
## Responses

| Response code |                           Raised by                           | Details |
|:-------------:|:-------------------------------------------------------------:|:-------:|
|      200      | The request is successful                                     |   More  |
|      201      | The post request is successful                                |   More  |
|      204      | The request is successful and the response has an empty body  |   More  |
|      400      | Form error assertion                                          |   More  |
|      403      | User is not allowed to perform this request                   |   More  |
|      404      | The resource requested was not found                          |   More  |
|      405      | The method used is not allowed on the route requested         |   More  |
|      409      | The request is successful but the data submitted is not valid |   More  |
 
When you perform a request to Philarmony, it will always send a response containing a serialized JSON and one of the HTTP code listed above.
 
## How it works

### Database schema

Your database schema is fully designed by 3 yaml files. 

#### Entity

An Entity is what could be view as a MySQL table, a main container of data. By default, it is defined by an owner (the User who created the entity), a kind, the date it was create and its properties.

 * [Read more](Resources/documentation/DatabaseSchema/ENTITY.md)

#### Property

The property is what could be view as a MySQL column, the container of a specific data. By default, it is defined by a type, a `unique` paremeter and a `required` parameter.

 * [Read more](Resources/documentation/DatabaseSchema/PROPERTY.md)

#### Enumeration

The EntityJoin is used to create relation between entities. As the Entity, it is defined by default by its owner, a kind and its properties but also a relation kind and 1 or 2 entities associated.

 * [Read more](Resources/documentation/DatabaseSchema/ENTITYJOIN.md)


### Rule manager

#### Access rules

 * [Read more](Resources/documentation/RuleManager/ACCESSRULE.md)

#### Forbidden rules

 * [Read more](Resources/documentation/RuleManager/FORBIDDENRULE.md)
 

## Road map

* Data validation : in some cases, the data of an entity must pass through validation (either automatic or manual) before being set public and accessible
* Test the bundle : to ensure the quality of the bundle and its sturdyness, tests will be written
