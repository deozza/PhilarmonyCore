Validation state
=

Validation state is used in order to assert an entity is in good conditions to be used. Each state defines specific methods usable by specific users.

Validation state are incremental. An entity cannot pass from the state 1 directly to the state 3 : it will need to validate its state 2 before.

By default, all your entities must have a `__default` state : 

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

_That state is mainly used to create a resource of the given entity kind._

## Add a new validation state

In order to add a new validation state, simply add it below the `states` tag, after the `_default`. 

##Validation process

1. Get the current state of the entity
2. Check if the method used is available in the state
3. Check if the user making the request corresponds with the user or the user group allowed
4. Check if there is a next step
5. Try to validate the constraints of the next step. If the constraints are filled, the current step is incremented

The last two steps are repeated until there is no more validate step reachable or the constraints are not filled.

## Example

Here is an example of an entity with all its states :

```yaml
entities:
  offer:
    properties: [title, description, price, annonce_category, photo, nbPersonMax]
    states:
      __default:
        methods:
          POST:
            properties: [title, description, price, annonce_category, nbPersonMax]
            by:
              roles: [ROLE_USER]
      posted:
        methods:
          POST:
            properties: [description, photo]
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
          PATCH:
            properties: all
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
          GET:
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
          DELETE:
            properties: [description, photo]
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
      public:
        constraints:
          manual:
            by:
              roles: [ROLE_ADMIN]
        methods:
          POST:
            properties: [description, photo]
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
          PATCH:
            properties: all
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
          GET:
            by: all
          DELETE:
            properties: [description, photo]
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
```