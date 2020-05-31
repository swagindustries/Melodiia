<?php

namespace Biig\Melodiia\Serialization\Json;

use Biig\Melodiia\Response\AbstractUserDataErrorResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ErrorNormalizer implements NormalizerInterface
{
    /**
     * @param AbstractUserDataErrorResponse $object
     * @param null                          $format
     *
     * @return array|bool|float|int|string
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $res = ['violations' => []];

        foreach ($object->getErrors() as $error) {
            $res['violations'][$error->getPropertyPath()] = $error->getErrors();
        }

        return $res;
    }

    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof AbstractUserDataErrorResponse;
    }
}
