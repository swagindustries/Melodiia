<?php

namespace Biig\Melodiia\Response\Listener;

use Biig\Melodiia\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SerializeOnKernelResponse.
 *
 * Render JSON Response on API response objects.
 */
class SerializeOnKernelView implements EventSubscriberInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'onKernelView',
        ];
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();
        if (!$response instanceof ApiResponse) {
            return;
        }

        $event->setResponse(new JsonResponse($this->serializer->serialize($response, 'json'), $response->httpStatus(), [], true));
    }
}
