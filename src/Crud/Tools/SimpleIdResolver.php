<?php

namespace SwagIndustries\Melodiia\Crud\Tools;

use SwagIndustries\Melodiia\Exception\IdMissingException;
use Symfony\Component\HttpFoundation\Request;

class SimpleIdResolver implements IdResolverInterface
{
    public function resolveId(Request $request, string $melodiiaModel): string
    {
        if ($request->attributes->has('id')) {
            return $request->attributes->get('id');
        }

        $melodiiaModel = (new \ReflectionClass($melodiiaModel))->getShortName();

        $modelId = \lcfirst($melodiiaModel) . 'Id';
        if ($request->attributes->has($modelId)) {
            return $request->attributes->get($modelId);
        }

        $idModel = 'id' . $melodiiaModel;
        if ($request->attributes->has($idModel)) {
            return $request->attributes->get($idModel);
        }

        throw new IdMissingException('Cannot find id in given request. Please provide a custom id resolver to this crud action.');
    }
}
