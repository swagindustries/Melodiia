<?php

namespace Biig\Melodiia\Test\Response\Listener;

use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\Listener\SerializeOnKernelView;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class SerializeOnKernelViewTest extends TestCase
{
    /** @var SerializerInterface|ObjectProphecy */
    private $serializer;

    /** @var SerializeOnKernelView */
    private $listener;

    public function setUp()
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->listener = new SerializeOnKernelView($this->serializer->reveal());
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
        };
        $this->serializer->serialize($response, Argument::cetera())->shouldBeCalled()->willReturn('"hello"');
        $event = new ViewEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            HttpKernelInterface::MASTER_REQUEST,
            $response
        );

        $this->listener->onKernelView($event);
    }
}
