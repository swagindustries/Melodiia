Using Symfony form types with Melodiia
======================================

Melodiia provides an `AbstractType` that you should extends to define you own types.

In addition, it provides some types that are very specific to API needs:
- BooleanType
- DateTimeType
- CollectionType

Those types are simple and behave just like you can expect. Excepted for the CollectionType.

The CollectionType
------------------

This type is very similar to the CollectionType of Symfony but regardless the original one, it supports APIs.

Here is a list of options you can use on it:
- `allow_add` (default to `true`): Allow the user to add items to the collection
- `allow_delete` (default to `true`): Allow the user to remove items from the collection
- `entry_type` (default to `TextType::class`): A form type that represent a collection item
- `entry_options` (default to `[]`): Options of the `entry_type`
