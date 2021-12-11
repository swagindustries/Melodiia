Configuration Reference of Melodiia
===================================

Here is the whole melodiia configuration commented:

```yaml
melodiia:
    apis:
        # Define as much APIs you want here.
        main:
            base_path: /api/v1
            openapi_path: /path/to/your/openapi/doc.yaml
            pagination:
                # Using this query attribute you can change the max per page
                max_per_page_attribute: max_per_page
```

