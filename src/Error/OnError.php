<?php

namespace Biig\Melodiia\Error;

use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class OnError
{
    /** @var SerializerInterface */
    private $serializer;

    /** @var string[] For example [ExceptionInterface::class => Response::HTTP_BAD_REQUEST] */
    private $exceptionToStatus;

    public function __construct(SerializerInterface $serializer, array $exceptionToStatus = [])
    {
        $this->serializer = $serializer;
        $this->exceptionToStatus = $exceptionToStatus;
    }

    public function __invoke(FlattenException $exception): Response
    {
        $exceptionClass = $exception->getClass();
        $statusCode = $exception->getStatusCode();
        foreach ($this->exceptionToStatus as $class => $status) {
            if (is_a($exceptionClass, $class, true)) {
                $statusCode = $status;
                break;
            }
        }

        $headers = $exception->getHeaders();
        $headers['Content-Type'] = sprintf('application/json; charset=utf-8');
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'deny';

        return new Response($this->serializer->serialize($exception, 'json', ['statusCode' => $statusCode]), $statusCode, $headers);
    }
}
