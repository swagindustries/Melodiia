<?php

namespace Biig\Melodiia\Crud\Persistence;

use Biig\Melodiia\Crud\FilterCollection;
use Pagerfanta\Pagerfanta;

interface DataStoreInterface
{
    public function save(object $model);

    /**
     * @param string     $type
     * @param string|int $id
     *
     * @return object|null
     */
    public function find(string $type, $id): ?object;

    /**
     * @param string           $type
     * @param int              $page
     * @param FilterCollection $filters
     * @param int              $maxPerPage
     *
     * @return Pagerfanta
     */
    public function getPaginated(string $type, int $page, FilterCollection $filters, $maxPerPage = 30): PagerFanta;
}
