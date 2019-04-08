Philarmony
=

## Table of contents

 * [About](#About)
 * [Installation](#Installation)
 * [Configuration](#Configuration)
 * [Example of usage](#Example-of-usage)
 * [How it works](#How-it-works)

## About

Philarmony is a bundle designed to help you creating easily a REST API, in an extensible, flexible and modular way. 

## Installation

You can install using composer, assuming it is already installed globally :
`composer require deozza\philarmony-bundle`

## Configuration

In order yo use Philarmony in your project, you need to configure it. First, create a `philarmony.yaml` file with the following structure : 

```yaml
deozza_philarmony:
    directory:
      entity: ~
      entityJoin: ~
      property: ~
      type: ~
```

This will be used to locate the database schema files. By default they are created and stored in `/var/Philarmony/`. 

Then, to use the embedded controllers of Philarmony, you need to enable them in your `/config/services.yaml` and in your `config/routes/annotations.yaml` :
```yaml
services: 
    Deozza\PhilarmonyBundle\Controller:
      resource: '@Deozza\PhilarmonyBundle\Controller'
      tags: ['controller.servcie_arguments']
```

```yaml
philarmony_controllers:
    resource: '@Deozza\PhilarmonyBundle\Controller'
    type: annotation
    prefix: /
```

## Example of usage

## How it works

### Database schema



#### Entity

An Entity is what could be view as a MySQL table, a main container of data. By default, it is defined by an owner (the User who created the entity), a kind and its properties.

 * [Read more](Documentation/DatabaseSchema/ENTITY.md)

#### EntityJoin

The EntityJoin is used to create relation between entities. As the Entity, it is defined by default by its owner, a kind and its properties but also a relation kind and 1 or 2 entities associated.

 * [Read more](Documentation/DatabaseSchema/ENTITYJOIN.md)

#### Property

The property is what could be view as a MySQL column, the container of a specific data. By default, it is defined by a type, a `unique` paremeter, a `is_required` parameter and a value.

 * [Read more](Documentation/DatabaseSchema/PROPERTY.md)

#### Type

The Type defines what kind of data is handled by a Property. To do so, it is composed by default of a RegExp.

 * [Read more](Documentation/DatabaseSchema/TYPE.md)

### Rule manager

#### Access rules

 * [Read more](Documentation/RuleManager/ACCESSRULE.md)

#### Forbidden rules

 * [Read more](Documentation/RuleManager/FORBIDDENRULE.md)
