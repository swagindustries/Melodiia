<?php

namespace Biig\Melodiia\Crud\Persistence;

use Biig\Melodiia\Crud\FilterInterface;
use Pagerfanta\Pagerfanta;

interface DataStoreInterface
{
    public function save(object $model);

    /**
     * @param string     $type
     * @param string|int $id
     * @return null|object
     */
    public function find(string $type, $id): ?object;

    /**
     * @param string             $type
     * @param int                $page
     * @param int                $maxPerPage
     * @param FilterInterface[]  $filters
     * @return Pagerfanta
     */
    public function getPaginated(string $type, int $page, $maxPerPage = 30, array $filters = []): PagerFanta;
}
