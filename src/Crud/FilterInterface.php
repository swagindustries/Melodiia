<?php

namespace Biig\Melodiia\Crud;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

interface FilterInterface
{
    /**
     * Takes a query object as parameter. It will be a QueryBuilder in the case of Doctrine usage.
     *
     * @param mixed $queryBuilder The object managed name is `item` inside the given query builder
     */
    public function filter($queryBuilder, FormInterface $form): void;

    /**
     * The filter support the class/entity/resource or not.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supports(string $class): bool;

    /**
     * Adds its data to the form.
     *
     * @param FormBuilderInterface $formBuilder
     */
    public function buildForm(FormBuilderInterface $formBuilder): void;
}
