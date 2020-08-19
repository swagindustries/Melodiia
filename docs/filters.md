Using filters
=============

To make filters you must implement the interface `FilterInterface`. Because Melodiia validates **any** input to avoid
inconsistent API, filters also uses form types.

If it's not done automatically, you must register your filter as a service:

```yaml
services:
    App\Filters\Todos\TodoContainsFilter:
        # This tag is autoconfigured in standard installations
        tags: ['melodiia.crud_filter']
```

Here is how may look this filter:

```php
class TodoContainFilter implements FilterInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function filter($queryBuilder, FormInterface $form): void
    {
        $q = $form->get('q')->getData();
        if (!empty($q)) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('item.content', ':like'));
            $queryBuilder->setParameter('like', '%' . $q . '%');
        }
    }

    public function supports(string $class): bool
    {
        return Todo::class === $class;
    }

    public function buildForm(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add('q', TextType::class);
    }
}
```
