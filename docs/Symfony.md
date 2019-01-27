Symfony integration
===================

I. Dependencies
---------------

Melodiia does not provide Symfony dependencies. If you are **not** using the
Symfony full edition (which is recommended) here are the components you're
going to need to install.

```bash
composer req symfony/form symfony/asset
```


II. Configure
-------------

Add it as bundle. Modify `bundles.php` file and add the following:

```php
// config/bundles.php
return [
    // Some other bundles already registered here
    // ...
    
    Biig\Melodiia\Bridge\Symfony\MelodiiaBundle::class => ['all' => true],
];
```

By default it comes with a configuration that will consider `/` is your API.
It's recommended to change it.

```yaml
melodiia:
    apis:
        main:
            # All those options are used for documentation purpose.
            paths: ['%kernel.project_dir%/src'] # List of path where melodiia will look for documentation blocks
            enable_doc: true
            base_path: /                        # Path to your API
            title: Awesome API                  
            description: |
                This is an awesome API. Really.
            version: 1.0.0
        # Melodiia comes with some form extensions, it makes your life easier
        # but in some case this could break some parts of an existing application
        # or just not act like you expect. That's why you can't disable extensions.
        form_extensions:
            # enabled by default, set false to disable.
            datetime: true
```
