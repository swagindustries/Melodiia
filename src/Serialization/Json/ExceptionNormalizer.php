<?php

namespace Biig\Melodiia\Serialization\Json;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExceptionNormalizer implements NormalizerInterface
{
    /** @var bool */
    private $debug;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = [
            'title' => 'An error occurred',
            'detail' => $this->getErrorMessage($object, $context, $this->debug),
        ];

        if ($this->debug && null !== $trace = $object->getTrace()) {
            $data['trace'] = $trace;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \Exception || $data instanceof FlattenException;
    }

    /**
     * This method is from ApiPlatform core
     */
    private function getErrorMessage($object, array $context, bool $debug = false): string
    {
        $message = $object->getMessage();
        if ($debug) {
            return $message;
        }
        if ($object instanceof FlattenException) {
            $statusCode = $context['statusCode'] ?? $object->getStatusCode();
            if ($statusCode >= 500 && $statusCode < 600) {
                $message = Response::$statusTexts[$statusCode];
            }
        }
        return $message;
    }
}
