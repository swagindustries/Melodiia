<?php

namespace Biig\Melodiia\Crud\Persistence;

interface DataStoreInterface
{
    public function save(object $model);
}
