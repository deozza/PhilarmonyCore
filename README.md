Philarmony
=

[![php](https://img.shields.io/badge/php-%5E7.2-blue.svg)]()
[![symfony](https://img.shields.io/badge/symfony-%5E4.2-blue.svg)](https://symfony.com/doc/current/index.html#gsc.tab=0)
[![Build Status](https://travis-ci.org/deozza/atypikhouse.svg?branch=master)](https://travis-ci.org/deozza/atypikhouse)
[![Stable](https://img.shields.io/badge/stable-1.0-brightgreen.svg)](https://github.com/deozza/Philarmony/tree/1.0.0)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)]()

## Table of contents

 * [About](#about)
 * [Installation](#installation)
 * [Configuration](#configuration)
 * [Example of usage](#example-of-usage)
 * [How it works](#how-it-works)
 * [Tests](#tests)
 * [Road map](#road-map)

## About

Philarmony is a bundle made to help you create a modular REST API. From database to controllers and forms, without forgetting authorization and data validation, manage your API easily in minutes.

## Installation

You can install using composer, assuming it is already installed globally :

`composer require deozza/philarmony-bundle`

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
    Deozza\PhilarmonyBundle\Controller\:
      resource: '@DeozzaPhilarmonyBundle/Controller'
      tags: ['controller.service_arguments']
      
    Deozza\PhilarmonyBundle\Repository\:
      resource: '@DeozzaPhilarmonyBundle/Repository'
      tags: ['doctrine.service_entity']  
```

```yaml
philarmony_controllers:
    resource: '@DeozzaPhilarmonyBundle/Controller'
    type: annotation
    prefix: /
```

Finally, in order to use filters inside your query, add the following functions inside the `doctrine.yaml` configuration file :

```yaml
doctrine:
    orm:
        dql:
            string_functions:
                JSON_CONTAINS: Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains
                JSON_CONTAINS_PATH: Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContainsPath
                JSON_EXTRACT: Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonExtract
                JSON_UNQUOTE: Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonUnquote
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
     "description": [
        "Located in central Tokyo. 2 bedrooms, bathroom, wifi.",
        "Animals are allowed"
        ],
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

 * [Read more](src/Resources/documentation/DatabaseSchema/ENTITY.md)

#### Property

The property is what could be view as a MySQL column, the container of a specific data. By default, it is defined by a type, a `unique` paremeter and a `required` parameter.

 * [Read more](src/Resources/documentation/DatabaseSchema/PROPERTY.md)

#### Enumeration

The enumeration is a list of possible values handled by of property. 

 * [Read more](src/Resources/documentation/DatabaseSchema/ENUMERATION.md)

### Validation state

Validation state is used in order to assert an entity is in good conditions to be used. Each state defines specific methods usable by specific users.

 * [Read more](src/Resources/documentation/Validation/VALIDATIONSTATE.md)

#### Authorization

To assure the right user is manipulating a resource, authorization is handled by the validation state. It determines which users are allowed to send the different requests.

 * [Read more](src/Resources/documentation/Validation/AUTHORIZATION.md)

#### Constraints

In order to pass from one state to the next, the entity and its data must be valid according to constraints.

 * [Read more](src/Resources/documentation/Validation/CONSTRAINS.md)
 
## Tests 

A demo app has been developped and is visible [here](https://github.com/deozza/atypikhouse). Through the tests of this app, we ensure that every feature of Philarmony is tested and we garanty the stability of the bundle.

## Road map

* Event handler : in some case, manipulating the data could trigger events
