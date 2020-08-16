<?php

namespace SwagIndustries\Melodiia\Crud;

interface FilterCollectionFactoryInterface
{
    public function createCollection(string $type): FilterCollectionInterface;
}
