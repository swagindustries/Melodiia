<?php

namespace Biig\Happii\Test\Response\Listener;

use Biig\Happii\Response\ApiResponse;
use Biig\Happii\Response\Listener\SerializeOnKernelView;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
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
        $event = $this->prophesize(GetResponseForControllerResultEvent::class);
        $event->getControllerResult()->willReturn($response);
        $event->setResponse(Argument::type(JsonResponse::class), Argument::cetera())->shouldBeCalled();

        $this->listener->onKernelView($event->reveal());
    }
}
