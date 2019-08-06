<?php

namespace Biig\Melodiia\Crud;

interface FilterCollectionFactoryInterface
{
    public function createCollection(string $type): FilterCollectionInterface;
}
