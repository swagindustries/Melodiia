<?php

namespace SwagIndustries\Melodiia\Crud\Persistence;

use SwagIndustries\Melodiia\Crud\FilterCollectionInterface;
use Pagerfanta\Pagerfanta;

interface DataStoreInterface
{
    public function save(object $model);

    public function remove(object $model);

    /**
     * @param string|int $id
     */
    public function find(string $type, $id): ?object;

    /**
     * @param int $maxPerPage
     */
    public function getPaginated(string $type, int $page, FilterCollectionInterface $filters, $maxPerPage = 30): PagerFanta;
}
