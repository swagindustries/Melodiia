<?php

namespace Biig\Melodiia\Test\Error;

use Biig\Melodiia\Error\OnError;
use Biig\Melodiia\Exception\MelodiiaRuntimeIssueException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;

class OnErrorTest extends TestCase
{
    /** @var SerializerInterface|ObjectProphecy */
    private $serializer;

    public function setUp()
    {
        $this->serializer = $this->prophesize(SerializerInterface::class);
        $this->serializer->serialize(Argument::cetera())->willReturn('{}');
    }

    public function testItReturnsAccurateErrors()
    {
        $exception = $this->prophesize(class_exists(FlattenException::class) ? FlattenException::class : LegacyFlattenException::class);
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
        $exception = $this->prophesize(class_exists(FlattenException::class) ? FlattenException::class : LegacyFlattenException::class);
        $exception->getClass()->willReturn(MelodiiaRuntimeIssueException::class);
        $exception->getStatusCode()->willReturn(500);
        $exception->getHeaders()->willReturn([]);

        $response = (new OnError($this->serializer->reveal(), [MelodiiaRuntimeIssueException::class => 418]))($exception->reveal());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent());
    }
}
