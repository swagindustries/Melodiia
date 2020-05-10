<?php

namespace Biig\Melodiia\Test\Bridge\Symfony\EventListener;

use Biig\Melodiia\Bridge\Symfony\EventListener\ExceptionListener;
use Biig\Melodiia\MelodiiaConfigurationInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    public function testItDoesNothingIfNotMelodiiaRoute()
    {
        /** @var MelodiiaConfigurationInterface|ObjectProphecy $config */
        $config = $this->prophesize(MelodiiaConfigurationInterface::class);
        $config->getApiEndpoints()->shouldBeCalled()->willReturn(['/api/v1/']);
        $config = $config->reveal();

        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        $request->getRequestUri()->shouldBeCalled()->willReturn('/random-url');
        $request = $request->reveal();

        // BC Layer for Symfony 4.3
        $event = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new \Exception('Fake exception for Melodiia tests.')
        );

        $listener = null;
        if (class_exists('Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener')) {
            $listener = new ErrorListener('foo');
        }

        $subject = new ExceptionListener($config, 'controller', null, false, $listener);
        $subject->onKernelException($event);
    }
}
