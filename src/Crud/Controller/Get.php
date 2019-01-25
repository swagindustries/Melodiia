<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\NotFound;
use Biig\Melodiia\Response\OkContent;
use Symfony\Component\HttpFoundation\Request;

final class Get implements CrudControllerInterface
{
    /** @var DataStoreInterface */
    private $dataStore;

    public function __construct(DataStoreInterface $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    public function __invoke(Request $request, $id): ApiResponse
    {
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $groups = $request->attributes->get(self::SERIALIZATION_GROUP, []);

        $data = $this->dataStore->find($modelClass, $id);

        if (null === $data) {
            return new NotFound();
        }

        return new OkContent($data, $groups);
    }
}
