<?php

namespace Biig\Melodiia\Serialization\Json;

use Biig\Melodiia\Response\OkContent;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGenerator;
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

    /** @var RequestStack */
    private $requestStack;

    public function __construct(NormalizerInterface $normalizer, RequestStack $requestStack)
    {
        $this->decorated = $normalizer;
        $this->requestStack = $requestStack;
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

        // Simple object case
        if (!$object->isCollection()) {
            return $this->decorated->normalize($object->getContent(), $format, $context);
        }

        // Collection case
        $content = $object->getContent();
        $result = [];

        // Pagination case
        if ($content instanceof Pagerfanta) {
            $result['meta'] = ['totalPages' => $content->getNbPages()];
            $uri = $this->requestStack->getMasterRequest()->getUri();
            $previousPage = null;
            $nextPage = null;

            if ($content->hasPreviousPage()) {
                $previousPage = \preg_replace('/([?&])page=(\d+)/', '$1page=' . $content->getPreviousPage(), $uri);
            }
            if ($content->hasNextPage()) {
                $nextPage = \preg_replace('/([?&])page=(\d+)/', '$1page=' . $content->getNextPage(), $uri);
            }

            $result['links'] = [
                'prev' => $previousPage,
                'next' => $nextPage,
                'last' => \preg_replace('/([?&])page=(\d+)/', '$1page=' . $content->getNbPages(), $uri),
                'first' => \preg_replace('/([?&])page=(\d+)/', '$1page=1', $uri)
            ];
        }

        $result['data'] = [];
        foreach ($content as $item) {
            $result['data'][] = $this->decorated->normalize($item, $format, $context);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $data instanceof OkContent;
    }
}
