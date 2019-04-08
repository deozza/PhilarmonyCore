Philarmony
=

##Table of contents

##About

##Installation

You can install using composer, assuming it is already installed globally :
`composer require deozza\philarmony-bundle`

##Configuration

```yaml
deozza_philarmony:
    directory:
      entity: ~
      entityJoin: ~
      property: ~
      type: ~
```

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

