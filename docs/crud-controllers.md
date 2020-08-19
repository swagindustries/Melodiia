About CRUD Controllers
======================

⚠️ By default Melodiia provides a doctrine integration. But if you didn't install Doctrine, it will not register
CRUD controllers. Be sure Doctrine is installed.

Here are all the controllers for your CRUD.

| CRUD action       | Service name                     |
|-------------------|----------------------------------|
| Create            | melodiia.crud.controller.create  |
| Read (collection) | melodiia.crud.controller.get_all |
| Read (item)       | melodiia.crud.controller.get     |
| Update            | melodiia.crud.controller.update  |
| Delete            | melodiia.crud.controller.delete  |

CRUD Controllers have options, here are a list of them and their availability by controller:

| Option name                             | Create | Read | Update | Delete |
|-----------------------------------------|--------|------|--------|--------|
| melodiia_model                          | x      | x    | x      | x      |
| melodiia_form                           | x      |      | x      |        |
| melodiia_clear_missing                  | x      |      | x      |        |
| melodiia_serialization_group            |        | x    |        |        |
| melodiia_security_check                 | x      | x    | x      | x      |
| melodiia_max_per_page                   |        | x    |        |        |
| melodiia_max_per_page_allowed           |        | x    |        |        |
| melodiia_allow_user_define_max_per_page |        | x    |        |        |

Example of route using options:

```yaml
acme_article_get_collection:
    path: /api/v1/articles
    controller: 'melodiia.crud.controller.get_all'
    methods: 'GET'
    defaults:
        melodiia_model: App\Entity\Article
        melodiia_serialization_group: "list-article"
        melodiia_max_per_page: 25
```
