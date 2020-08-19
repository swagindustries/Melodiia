<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response\Listener;

use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Serialization\Context\ContextBuilderChainInterface;
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

    /** @var ContextBuilderChainInterface */
    private $contextBuilderChain;

    public function __construct(SerializerInterface $serializer, ContextBuilderChainInterface $contextBuilderChain)
    {
        $this->serializer = $serializer;
        $this->contextBuilderChain = $contextBuilderChain;
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

        $context = $this->contextBuilderChain->buildContext([], $response);

        $event->setResponse(
            new JsonResponse(
                $this->serializer->serialize($response, 'json', $context),
                $response->httpStatus(),
                $response->headers(),
                true
            )
        );
    }
}
