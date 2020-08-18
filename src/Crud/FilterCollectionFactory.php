<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud;

use Symfony\Component\Form\FormFactoryInterface;

class FilterCollectionFactory implements FilterCollectionFactoryInterface
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var FilterInterface[] */
    protected $filters;

    public function __construct(FormFactoryInterface $formFactory, iterable $filters = [])
    {
        $this->formFactory = $formFactory;
        $this->filters = $filters;
    }

    public function createCollection(string $type): FilterCollectionInterface
    {
        $filters = $this->getInstance();

        foreach ($this->filters as $filter) {
            if ($filter->supports($type)) {
                $filters->add($filter);
            }
        }

        return $filters;
    }

    protected function getInstance(): FilterCollectionInterface
    {
        return new FilterCollection($this->formFactory, []);
    }
}
