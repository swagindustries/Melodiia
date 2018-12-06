Happii
======

Finally some competitor to ApiPlatform. And you're gonna like it.


Features
--------

- Adds a documentation endpoint with help of swagger
- Set of api responses
- Uses Symfony Form as input, because Symfony Serializer is not ready for that

Install
-------

```bash
composer require biig/happii
```

### Symfony setup

Add the bundle to your bundle list:

```php
Biig\Happii\Bridge\Symfony\HappiiBundle::class => ['all' => true]
```

Setup the configuration

```yaml
# config/packages/happii.yaml

happii:
    apis:
        # Choose the name of your api here
        main: ~
```

Add happii routing:

```yaml
admin_routes:
    resource: 'happii.routing_loader:loadRoutes'
    type: service
```


FAQ
---

### Blank page on documentation

You probably forget to install assets. `bin/console assets:install`.
