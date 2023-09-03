<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Serialization\Json;

use SwagIndustries\Melodiia\Response\Created;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CreatedNormalizer implements NormalizerInterface
{
    public function normalize($object, string $format = null, array $context = []): array
    {
        $res = [];
        $resource = $object->getResourceId();

        if (null !== $resource) {
            $res['resource'] = $resource;
        }
        $res['id'] = $object->getId();

        return $res;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return is_object($data) && $data instanceof Created;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Created::class => true,
        ];
    }
}
