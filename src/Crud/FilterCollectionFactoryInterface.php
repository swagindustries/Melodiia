<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud;

interface FilterCollectionFactoryInterface
{
    public function createCollection(string $type): FilterCollectionInterface;
}
