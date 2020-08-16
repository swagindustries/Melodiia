Melodiia
========

Finally some competitor to ApiPlatform.

Features
--------

- Adds a documentation endpoint with help of swagger
- Set of api responses
- Uses Symfony Form as input, because Symfony Serializer is not ready for that

Install
-------

```bash
composer require swag-industries/melodiia
```

### Symfony setup

Add the bundle to your bundle list:

```php
Biig\Melodiia\Bridge\Symfony\MelodiiaBundle::class => ['all' => true]
```

Setup the configuration:

```yaml
# config/packages/melodiia.yaml

melodiia:
    apis:
        # Choose the name of your api here
        main: ~
```

Add melodiia routing:

```yaml
admin_routes:
    resource: 'melodiia.routing_loader:loadRoutes'
    type: service
```

### Documentation 

 - [Configure my first crud operations !](./docs/Crud.md)
 - [Integration with Symfony ](./docs/Symfony.md)
 - [Presentation of Melodiia @Biig-io](https://docs.google.com/presentation/d/1dtxUOzZFGRq7Ar5YV5aZ6AN60RhDbf_0OcXKj5iiDS8/edit?usp=sharing)

Feel free to open an issue, if you encounter problems implementing Melodiia

FAQ
---

### Blank page on documentation

You probably forget to install assets. `bin/console assets:install`.
