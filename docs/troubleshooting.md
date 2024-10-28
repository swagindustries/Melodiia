# Troubleshooting

_Here is a list of classic error you may experiment with Melodiia and how to fix it_


### Blank page on documentation

You probably forget to install assets. `bin/console assets:install`.

## Uncaught PHP Exception Symfony\Component\Serializer\Exception\CircularReferenceException

This error is disturbing but not related to Melodiia. It happens because you return an object that have a reference to
itself in one of its children (or sub-children).

Let's consider the following couple of entities:

```php
class Product
{
    public ArrayCollection $variants;
}

class Variant
{
    public Product $product;
}
```

When Melodiia tries to serialize your product, it will also serialize the variants associated to the product, then the product itself.

The serializer is well-designed and detect this issue, this is why you're getting this exception instead of an infinite loop!

To fix it, you need to use serialization groups, it's
[documented on Symfony documentation](https://symfony.com/doc/current/serializer.html#using-serialization-groups-attributes).
