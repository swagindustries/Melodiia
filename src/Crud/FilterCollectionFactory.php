<?php

namespace Biig\Melodiia\Crud;

use Symfony\Component\Form\FormFactoryInterface;

class FilterCollectionFactory implements FilterCollectionFactoryInterface
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FilterInterface[] */
    private $filters;

    public function __construct(FormFactoryInterface $formFactory, iterable $filters = [])
    {
        $this->formFactory = $formFactory;
        $this->filters = $filters;
    }

    public function createCollection(string $type): FilterCollection
    {
        $filters = new FilterCollection($this->formFactory, []);

        foreach ($this->filters as $filter) {
            if ($filter->supports($type)) {
                $filters->add($filter);
            }
        }

        return $filters;
    }
}
