<?php

namespace Biig\Melodiia\Test\Serialization\Json;

use Biig\Melodiia\Serialization\Json\ExceptionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FlattenException;

class ExceptionNormalizerTest extends TestCase
{
    public function testItSupportsExceptionAndFlattenException()
    {
        $normalizer = new ExceptionNormalizer();
        $this->assertTrue($normalizer->supportsNormalization($this->prophesize(FlattenException::class)->reveal()));
        $this->assertTrue($normalizer->supportsNormalization(new \Exception()));
        $this->assertFalse($normalizer->supportsNormalization(new \stdClass()));
    }

    public function testItNormalizeWithoutTraceByDefault()
    {
        $normalizer = new ExceptionNormalizer();
        $exception = $this->prophesize(FlattenException::class);
        $exception->getStatusCode()->willReturn(403);
        $exception->getMessage()->willReturn('dummy error message');
        $exception->getTrace()->shouldNotBeCalled();

        $res = $normalizer->normalize($exception->reveal());
        $this->assertArrayHasKey('title', $res);
        $this->assertEquals('dummy error message', $res['detail']);
    }

    public function testItNormalizeWithTraceInDebugMode()
    {
        $normalizer = new ExceptionNormalizer(true);
        $exception = $this->prophesize(FlattenException::class);
        $exception->getStatusCode()->willReturn(403);
        $exception->getMessage()->willReturn('dummy error message');
        $exception->getTrace()->shouldBeCalled()->willReturn([]);

        $res = $normalizer->normalize($exception->reveal());
        $this->assertArrayHasKey('title', $res);
        $this->assertArrayHasKey('trace', $res);
        $this->assertEquals('dummy error message', $res['detail']);
    }
}
