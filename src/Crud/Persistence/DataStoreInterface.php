<?php

namespace Biig\Melodiia\Crud\Persistence;

use Biig\Melodiia\Crud\FilterCollectionInterface;
use Pagerfanta\Pagerfanta;

interface DataStoreInterface
{
    public function save(object $model);
    public function remove(object $model);

    /**
     * @param string     $type
     * @param string|int $id
     *
     * @return object|null
     */
    public function find(string $type, $id): ?object;

    /**
     * @param string                    $type
     * @param int                       $page
     * @param FilterCollectionInterface $filters
     * @param int                       $maxPerPage
     *
     * @return Pagerfanta
     */
    public function getPaginated(string $type, int $page, FilterCollectionInterface $filters, $maxPerPage = 30): PagerFanta;
}
