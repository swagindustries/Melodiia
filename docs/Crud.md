CRUDs with Melodiia
===================

Register your first CRUD
------------------------

Melodiia CRUDs are **simple** controllers that you can configure in the routing.

For any further usage, consider using your own controller.

Here is an example of configuration:

#### Simple operation

```yaml
acme_article_create:
    path: /api/v1/articles
    controller: 'melodiia.crud.controller.create'
    methods: ['POST']
    defaults:
        melodiia_model: App\Entity\Article
        melodiia_form: App\Form\ArticleType

acme_article_update:
    path: /api/v1/articles/{id}
    controller: 'melodiia.crud.controller.update'
    methods: ['PATCH']
    defaults:
        melodiia_model: App\Entity\Article
        melodiia_form: App\Form\ArticleType

acme_article_get:
    path: /api/v1/articles/{id}
    controller: 'melodiia.crud.controller.get'
    methods: ['GET']
    defaults:
        melodiia_model: App\Entity\Article
        melodiia_security_check: 'some content runnable in AuthorizationChecker class'

```

#### Collection

The collection configuration is more advanced due to the use case, please consider following examples.

The key "melodiia_allow_user_define_max_per_page" default value is "false" 

```yaml
# The user can specify in query parameter under "max_per_page" key a maximum of 50 items per page
acme_article_get_collection:
    path: /api/v1/articles
    controller: 'melodiia.crud.controller.get_all'
    methods: ['GET']
    defaults:
        melodiia_model: App\Entity\Article
        melodiia_serialization_group: "list-article"
        melodiia_security_check: 'some content runnable in AuthorizationChecker class'
        melodiia_allow_user_define_max_per_page: true 
        melodiia_max_per_page_allowed: 50


# Static use case the user can't ask for a specific count of items per page. The max count of items returned will be 25
acme_article_get_collection2:
    path: /api/v1/articles
    controller: 'melodiia.crud.controller.get_all'
    methods: ['GET']
    defaults:
        melodiia_model: App\Entity\Article
        melodiia_serialization_group: "list-article"
        melodiia_max_per_page: 25
```

Learn more about available options in `CrudControllerInterface`.

Filters
-------

Filters are enabled for `GetAll` controllers. You can use them by yourself
if you consider using the `FilterCollectionFactory`.

All you have to do is to implement the interface `FilterInterface`.

Currently Doctrine query is the only type of query managed by the filters.

Here is an example of filter you may want to do:

```php
<?php
// TypeFilter

class TypeFilter implements \Biig\Melodiia\Crud\FilterInterface
{
    /** @var string[] */
    private $types;

    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function filter($query, Symfony\Component\Form\FormInterface $form): void
    {
        $type = $form->get('type')->getData();
        if ($type !== null) {
            $query
                ->andWhere($query->expr()->eq('item.type', ':filterType'))
                ->setParameter('filterType', $type)
            ;
        }
    }

    public function supports(string $class): bool
    {
        return $class === MyEntity::class;
    }

    public function buildForm(Symfony\Component\Form\FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add('type', ChoiceType::class, [
            'choices' => $this->types            
        ]); 
    }
}

```

> Note: the given query builder name the entity `item` no matter what kind of entity it is.
