<?php

namespace SwagIndustries\Melodiia\Crud;

use SwagIndustries\Melodiia\Exception\NoFormFilterCreatedException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterCollection implements FilterCollectionInterface
{
    /** @var FilterInterface[] */
    protected $filters;

    /** @var FormFactoryInterface */
    private $formFactory;

    /**
     * Cached form. This class is stateful.
     *
     * @var FormInterface
     */
    private $form;

    public function __construct(FormFactoryInterface $formFactory, array $filters)
    {
        $this->formFactory = $formFactory;
        $this->filters = [];
        foreach ($filters as $filter) {
            $this->add($filter);
        }
    }

    public function add(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    public function filter($query): void
    {
        if (null === $this->form) {
            throw new NoFormFilterCreatedException('The filter form was not generated. You probably forgot to call `$collection->getForm()->handleRequest($request)`.');
        }

        foreach ($this->filters as $filter) {
            $filter->filter($query, $this->getForm());
        }
    }

    public function getForm(): FormInterface
    {
        if ($this->form) {
            return $this->form;
        }

        $builder = $this->formFactory->createNamedBuilder('', FormType::class, null, [
            'method' => 'GET',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);

        foreach ($this->filters as $filter) {
            $filter->buildForm($builder);
        }

        return $this->form = $builder->getForm();
    }
}
