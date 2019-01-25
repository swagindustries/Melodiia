<?php

namespace Biig\Melodiia\Crud\Persistence;

interface DataStoreInterface
{
    public function save(object $model);

    public function find(string $type, $id): ?object;
}
