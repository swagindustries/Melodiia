<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Response\Listener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\Listener\SerializeOnKernelView;
use SwagIndustries\Melodiia\Serialization\Context\ContextBuilderChainInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class SerializeOnKernelViewTest extends TestCase
{
    /** @var SerializerInterface|ObjectProphecy */
    private $serializer;

    /** @var ContextBuilderChainInterface|ObjectProphecy */
    private $contextChain;

    /** @var SerializeOnKernelView */
    private $listener;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->contextChain = $this->prophesize(ContextBuilderChainInterface::class);
        $this->contextChain->buildContext(Argument::cetera())->willReturn([]);
        $this->listener = new SerializeOnKernelView($this->serializer->reveal(), $this->contextChain->reveal());
    }

    public function tearDown(): void
    {
        $this->serializer = null;
        $this->contextChain = null;
        $this->listener = null;
    }

    public function testItSubscribeOnKernelView()
    {
        $this->assertArrayHasKey(KernelEvents::VIEW, SerializeOnKernelView::getSubscribedEvents());
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    public function testItTransformApiResponse()
    {
        $response = new class() implements ApiResponse {
            public function httpStatus(): int
            {
                return 200;
            }

            public function headers(): array
            {
                return [];
            }
        };
        $this->serializer->serialize($response, Argument::cetera())->shouldBeCalled()->willReturn('"hello"');
        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $this->listener->onKernelView($event);

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
    }
}
