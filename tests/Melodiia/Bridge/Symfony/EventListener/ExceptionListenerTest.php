<?php

namespace Biig\Melodiia\Test\Bridge\Symfony\EventListener;

use Biig\Melodiia\Bridge\Symfony\EventListener\ExceptionListener;
use Biig\Melodiia\MelodiiaConfigurationInterface;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ExceptionListenerTest extends TestCase
{
    public function testItDoesNothingIfNotMelodiiaRoute()
    {
        /** @var MelodiiaConfigurationInterface|ObjectBehavior $config */
        $config = $this->prophesize(MelodiiaConfigurationInterface::class);
        $config->getApiEndpoints()->shouldBeCalled()->willReturn(['/api/v1/']);
        $config = $config->reveal();

        /** @var Request|ObjectBehavior $request */
        $request = $this->prophesize(Request::class);
        $request->getRequestUri()->shouldBeCalled()->willReturn('/random-url');
        $request = $request->reveal();

        $listener = new ExceptionListener($config, 'controller');
        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getRequest()->willReturn($request);
        $listener->onKernelException($event->reveal());
    }
}
