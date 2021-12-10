<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\EventListener;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Error\OnError;
use SwagIndustries\Melodiia\EventListener\ExceptionListener;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListenerTest extends TestCase
{
    public function testItDoesNothingIfNotMelodiiaRoute()
    {
        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        $request = $request->reveal();

        /** @var MelodiiaConfigurationInterface|ObjectProphecy $config */
        $config = $this->prophesize(MelodiiaConfigurationInterface::class);
        $config->getApiConfigFor($request)->shouldBeCalled()->willReturn(null);
        $config = $config->reveal();

        $event = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            new \Exception('Fake exception for Melodiia tests.')
        );
        $subject = new ExceptionListener($config, $this->getOnError(), false, $errorListener = $this->fakeErrorListener());
        $subject->onKernelException($event);
        $this->assertFalse($errorListener->hasBeenCalled());
    }

    public function testItHandleExceptionIfItsUnderAMelodiiaRoute()
    {
        /** @var Request|ObjectProphecy $request */
        $request = $this->prophesize(Request::class);
        $request = $request->reveal();

        /** @var MelodiiaConfigurationInterface|ObjectProphecy $config */
        $config = $this->prophesize(MelodiiaConfigurationInterface::class);
        $config->getApiConfigFor($request)->shouldBeCalled()->willReturn(['some' => 'config']);
        $config = $config->reveal();

        $event = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            1,
            new \Exception('Fake exception for Melodiia tests.')
        );
        $subject = new ExceptionListener($config, $this->getOnError(), false, $errorListener = $this->fakeErrorListener());
        $subject->onKernelException($event);

        $this->assertTrue($errorListener->hasBeenCalled());
    }

    private function fakeErrorListener()
    {
        return new class() extends ErrorListener {
            private $isCalled = false;

            public function __construct()
            {
                parent::__construct('');
            }

            public function onKernelException($event)
            {
                $this->isCalled = true;
            }

            public function hasBeenCalled()
            {
                return $this->isCalled;
            }
        };
    }

    private function getOnError()
    {
        $serializer = $this->prophesize(SerializerInterface::class);

        return new OnError($serializer->reveal());
    }
}
