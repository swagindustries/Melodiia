<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Serialization\Json;

use SwagIndustries\Melodiia\Response\AbstractUserDataErrorResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ErrorNormalizer implements NormalizerInterface
{
    /**
     * @param AbstractUserDataErrorResponse $object
     * @param null                          $format
     *
     * @return array|bool|float|int|string
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $res = ['violations' => []];

        foreach ($object->getErrors() as $error) {
            $res['violations'][$error->getPropertyPath()] = $error->getErrors();
        }

        return $res;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return is_object($data) && $data instanceof AbstractUserDataErrorResponse;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AbstractUserDataErrorResponse::class => true,
        ];
    }
}
