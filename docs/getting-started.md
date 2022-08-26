Getting Started
===============

_Notice: if you want to see a complete example, you can see the folder test/TestApplication that contains an app of test we
use for functional testing_

Installation
------------

Run the following command and you're all set:

```bash
composer require swag-industries/melodiia
```

Step 1: your model
------------------

To work with Melodiia, your model must implement the interface `MelodiiaModel`.

```php
/**
 * @ORM\Entity()
 */
class Todo implements MelodiiaModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;
}
```

Step 2: CRUD
------------

Because a lot of what you do is actually CRUD, Melodiia provides controllers to make a CRUD. You just have to use
your routing to make it work.

```yaml
# routing.yaml
get_todos:
    path: /todos
    controller: 'melodiia.crud.controller.get_all'
    methods: GET
    defaults:
        melodiia_model: App\Entity\Todo
```

This will generate a paginated result. You may also want to add [filters](filters.md) to the output.

Learn more about CRUD controllers on [related documentation page](crud-controllers.md).

Step 3: Form
------------

Melodiia uses form types for input data. That's how you manage your input validation and errors in the context of Melodiia.

```php
use SwagIndustries\Melodiia\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\Todo;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', TextType::class, ['constraints' => new NotBlank()]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Todo::class);
    }
}
```

You can now use the `Create` controller from the CRUD controllers.

By default Symfony Forms are not well-designed for API. That's why we created a new `AbstractType` that you should
extend. Melodiia also provide a set of [form types for APIs needs](form-types.md).

```yaml
# routing.yaml
post_todos:
    path: /todos
    controller: 'melodiia.crud.controller.create'
    methods: POST
    defaults:
        melodiia_model: App\Entity\Todo
        melodiia_form: App\Form\TodoType
```

Step 4: write documentation
---------------------------

_Disclaimer: Melodiia was originally generating the API documentation. I though this is an expected behavior from an API library.
But I quickly figured out that even if it was generating valid documentation (regardless some other frameworks), using docblocks
to configure the OpenAPI documentation in a good way was a serious pain and was just making things complicated._

**Melodiia doesn't generates automatically documentation.**

But it comes with some help to build your documentation:
1. Run `bin/console assets:insall`
2. Create a file `documentation.yml` (the `config` folder seems like a good location)
3. Add your documentation file in the global melodiia configuration and configure your documentation route

```yaml
# /config/packages/melodiia.yaml
melodiia:
    apis:
        main:
            # Required all the time! Do not forget this!
            base_path:    /api/v1
            
            # This is what we are looking for here:
            openapi_path: /path/to/your/openapi/doc.yaml
```

```yaml
# The documentation should be available only in dev environment in some cases
# /config/routing_dev.yaml
documentation:
    path: /documentation
    controller: 'melodiia.documentation'
```

⚠️ The render of the documentation cannot work without Twig. Be sure twig is installed.

Step 5: do what you want
------------------------

Just like the readme says. Melodiia is a set of tools. Feel free to make your own controllers, but if you want that
Melodiia process your content (to ensure your API always answer with the same format), you must return a response
that implements `SwagIndustries\Melodiia\Response\ApiResponse`.
