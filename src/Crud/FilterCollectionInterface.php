<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud;

use Symfony\Component\Form\FormInterface;

/**
 * This interface is used in context of Melodiia CRUD.
 * Which work with Symfony Form component.
 */
interface FilterCollectionInterface
{
    public function add(FilterInterface $filter): void;

    /**
     * Executes filters against a query.
     *
     * @param mixed $query
     */
    public function filter($query): void;

    /**
     * Creates the filter form.
     */
    public function getForm(): FormInterface;
}
