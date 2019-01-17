<?php

namespace Biig\Melodiia\Serialization\Json;

use Biig\Melodiia\Response\Created;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CreatedNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        $res = [];
        $resource = $object->getResourceId();

        if (null !== $resource) {
            $res['resource'] = $resource;
        }
        $res['id'] = $object->getId();

        return $res;
    }

    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof Created;
    }
}
