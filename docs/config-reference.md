Configuration Reference of Melodiia
===================================

```yaml
melodiia:
    apis:
        # Define as much APIs you want here.
        main:
            base_path: /api/v1
            pagination:
                # Using this query attribute you can change the max per page
                max_per_page_attribute: max_per_page
    # Melodiia comes with some form types to help you build your application. You can enable them here.
    # This is the default configuration
    form_extensions:
        datetime: true
```
