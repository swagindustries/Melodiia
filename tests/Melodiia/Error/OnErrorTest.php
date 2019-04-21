<?php

namespace Biig\Melodiia\Test\Error;

use Biig\Melodiia\Error\OnError;
use Biig\Melodiia\Exception\MelodiiaRuntimeIssueException;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class OnErrorTest extends TestCase
{
    /** @var SerializerInterface|ObjectBehavior */
    private $serializer;

    public function setUp()
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->serializer->serialize(Argument::cetera())->willReturn('{}');
    }

    public function testItReturnsAccurateErrors()
    {
        $exception = $this->prophesize(FlattenException::class);
        $exception->getClass()->willReturn(MelodiiaRuntimeIssueException::class);
        $exception->getStatusCode()->willReturn(500);
        $exception->getHeaders()->willReturn([]);

        $response = (new OnError($this->serializer->reveal()))($exception->reveal());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }

    public function testItBindsExceptionToErrorCode()
    {
        $exception = $this->prophesize(FlattenException::class);
        $exception->getClass()->willReturn(MelodiiaRuntimeIssueException::class);
        $exception->getStatusCode()->willReturn(500);
        $exception->getHeaders()->willReturn([]);

        $response = (new OnError($this->serializer->reveal(), [MelodiiaRuntimeIssueException::class => 418]))($exception->reveal());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }
}
