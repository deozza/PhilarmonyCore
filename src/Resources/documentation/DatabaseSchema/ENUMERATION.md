Enumeration
=

The enumeration is a list of possible values handled by of property. 

To link a property with an enumeration, specify its type with `enumeration.{name_of_enumeration}` like the following :

__Example : __

```yaml
properties:
      place_category:
            type: enumeration.place_category
            required: true
            unique: false

enumerations:
    place_category: ['house', 'appartment']
```