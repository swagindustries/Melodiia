<?php

namespace Biig\Melodiia\Serialization\Json;

use Biig\Melodiia\Response\OkContent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class OkContentNormalizer.
 *
 * Normalize only the content of OkContent object using the serialization
 * context contained in OkContent object.
 */
class OkContentNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    private $decorated;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->decorated = $normalizer;
    }

    /**
     * @param OkContent $object
     * @param string    $format
     * @param array     $context
     *
     * @return array|bool|float|int|string
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $groups = $object->getSerializationContext()->getGroups();
        if (!empty($context['groups'])) {
            $groups = array_merge($context['groups'], $groups);
        }

        if (!empty($groups)) {
            $context['groups'] = $groups;
        }

        return $this->decorated->normalize($object->getContent(), $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof OkContent;
    }
}
