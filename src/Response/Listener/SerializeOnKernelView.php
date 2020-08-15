<?php

namespace SwagIndustries\Melodiia\Response\Listener;

use SwagIndustries\Melodiia\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
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

    public function onKernelView(ViewEvent $event)
    {
        $response = $event->getControllerResult();
        if (!$response instanceof ApiResponse) {
            return;
        }

        $event->setResponse(
            new JsonResponse(
                $this->serializer->serialize($response, 'json'),
                $response->httpStatus(),
                [],
                true
            )
        );
    }
}
