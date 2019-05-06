Authorization
=

To assure the right user is manipulating a resource, authorization is handled by the validation state. It determines which users are allowed to send the different requests.

## Add a new usable method to a state

In order to add a method to a state, simply add it under the `methods` tag:

```yaml
states:
      state_name:
        methods:
          GET:
    
```

The possible methods are `GET`, `POST`, `PATCH` and `DELETE` .

In the case you add a `POST` or a `PATCH` method, you need to specify which properties will be used for the form. These properties must be set according to the properties of the entity.

```yaml
entities:
  offer:
    properties: [title, description, price, annonce_category, photo, nbPersonMax]
    states:
      __default:
        methods:
          POST:
            properties: [title, description, price, annonce_category, nbPersonMax]
```

## Allow a user 

You can allow a group of users, by their role, to manipulate an entity with a method. To do so, add their the role like in the following example :

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
```

You can also allow a specific user to manipulate an entity with a method. For now, you can only allow the owner of the entity. To do so, follow the example :

```yaml
entities:
  annonce:
    properties: [title, description, price, annonce_category, photo, nbPersonMax]
    states:
    # [...]
      posted:
        methods:
          POST:
            properties: [description, photo]
            by:
              users: [owner]
              roles: [ROLE_ADMIN]
```

In order to let all the users, logged in or not, to manipulate an entity, follow the example:
```yaml
entities:
  annonce:
    properties: [title, description, price, annonce_category, photo, nbPersonMax]
    states:
    # [...]
      public:
        methods:
          GET:
            by: all
```