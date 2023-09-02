<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Serialization\Json;

use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
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

    public function normalize($object, $format = null, array $context = []): array
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

    public function supportsNormalization($data, $format = null): bool
    {
        if (class_exists(FlattenException::class)) {
            return $data instanceof \Exception || $data instanceof FlattenException;
        }

        // BC Layer for Sf 4.x
        return $data instanceof \Exception || $data instanceof LegacyFlattenException;
    }

    /**
     * This method is from ApiPlatform core.
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
