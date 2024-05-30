<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Response\Listener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\Listener\SerializeOnKernelView;
use SwagIndustries\Melodiia\Serialization\Context\ContextBuilderChainInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class SerializeOnKernelViewTest extends TestCase
{
    use ProphecyTrait;

    /** @var SerializerInterface|ObjectProphecy */
    private $serializer;

    /** @var ContextBuilderChainInterface|ObjectProphecy */
    private $contextChain;

    /** @var SerializeOnKernelView */
    private $listener;

    /** @var ApiResponse */
    private $dummyResponse;

    public function setUp(): void
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->contextChain = $this->prophesize(ContextBuilderChainInterface::class);
        $this->contextChain->buildContext(Argument::cetera())->willReturn([]);
        $this->listener = new SerializeOnKernelView($this->serializer->reveal(), $this->contextChain->reveal());
        $this->dummyResponse = new class() implements ApiResponse {
            public function httpStatus(): int
            {
                return 200;
            }

            public function headers(): array
            {
                return [];
            }
        };
    }

    public function tearDown(): void
    {
        $this->serializer = null;
        $this->contextChain = null;
        $this->listener = null;
        $this->dummyResponse = null;
    }

    public function testItSubscribeOnKernelView()
    {
        $this->assertArrayHasKey(KernelEvents::VIEW, SerializeOnKernelView::getSubscribedEvents());
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    public function testItTransformApiResponse()
    {
        $this->serializer->serialize($this->dummyResponse, Argument::cetera())->shouldBeCalled()->willReturn('"hello"');
        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $this->dummyResponse
        );

        $this->listener->onKernelView($event);

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
    }

    public function testItDoesNotTransformSymfonyResponse()
    {
        $response = new Response('<h1>Containing HTML?</h1>');
        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );
        $this->listener->onKernelView($event);
        $this->assertSame(null, $event->getResponse());
    }

    public function testItSendsTheRightContentType()
    {
        $this->serializer->serialize($this->dummyResponse, Argument::cetera())->shouldBeCalled()->willReturn('"hello"');
        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            HttpKernelInterface::MAIN_REQUEST,
            $this->dummyResponse
        );

        $this->listener->onKernelView($event);

        $this->assertEquals($event->getResponse()->headers->get('content-type'), SerializeOnKernelView::CONTENT_TYPE);
    }
}
