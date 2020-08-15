<?php

namespace SwagIndustries\Melodiia\Test\Serialization\Json;

use SwagIndustries\Melodiia\Serialization\Json\ExceptionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class ExceptionNormalizerTest extends TestCase
{
    public function testItSupportsExceptionAndFlattenException()
    {
        $normalizer = new ExceptionNormalizer();
        $this->assertTrue($normalizer->supportsNormalization($this->prophesize(class_exists(FlattenException::class) ? FlattenException::class : LegacyFlattenException::class)->reveal()));
        $this->assertTrue($normalizer->supportsNormalization(new \Exception()));
        $this->assertFalse($normalizer->supportsNormalization(new \stdClass()));
    }

    public function testItNormalizeWithoutTraceByDefault()
    {
        $normalizer = new ExceptionNormalizer();
        $exception = $this->prophesize(class_exists(FlattenException::class) ? FlattenException::class : LegacyFlattenException::class);
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
        $exception = $this->prophesize(class_exists(FlattenException::class) ? FlattenException::class : LegacyFlattenException::class);
        $exception->getStatusCode()->willReturn(403);
        $exception->getMessage()->willReturn('dummy error message');
        $exception->getTrace()->shouldBeCalled()->willReturn([]);

        $res = $normalizer->normalize($exception->reveal());
        $this->assertArrayHasKey('title', $res);
        $this->assertArrayHasKey('trace', $res);
        $this->assertEquals('dummy error message', $res['detail']);
    }
}
