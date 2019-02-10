<?php

namespace Biig\Melodiia\Test\Serialization\Json;

use Biig\Melodiia\Response\OkContent;
use Biig\Melodiia\Serialization\Json\OkContentNormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OkContentNormalizerTest extends TestCase
{
    /** @var NormalizerInterface|ObjectProphecy */
    private $mainNormalizer;

    /** @var OkContentNormalizer */
    private $okContentNormalizer;

    public function setUp()
    {
        $this->mainNormalizer = $this->prophesize(NormalizerInterface::class);

        $this->okContentNormalizer = new OkContentNormalizer($this->mainNormalizer->reveal());
    }

    public function testItIsInstanceOfNormalizerInterface()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->okContentNormalizer);
    }

    public function testItReturnsDirectlyTheNormalizationOfContainedItem()
    {
        $okContent = new OkContent('foo');

        $this->mainNormalizer->normalize('foo', Argument::cetera())->willReturn('foo normalized')->shouldBeCalled();

        $res = $this->okContentNormalizer->normalize($okContent);

        $this->assertEquals('foo normalized', $res);
    }

    public function testItDoesNotEnforceEmptyGroupArray()
    {
        $okContent = new OkContent('foo');

        $this->mainNormalizer->normalize('foo', 'json', [])->willReturn('foo normalized')->shouldBeCalled();

        $res = $this->okContentNormalizer->normalize($okContent, 'json');

        $this->assertEquals('foo normalized', $res);
    }

    public function testItSupportsOnlyOkContentInstance()
    {
        $this->assertTrue($this->okContentNormalizer->supportsNormalization(new OkContent('foo')));
        $this->assertFalse($this->okContentNormalizer->supportsNormalization(new \stdClass()));
        $this->assertFalse($this->okContentNormalizer->supportsNormalization('foo'));
    }

    public function testItAddsGroupsToNormalizationContext()
    {
        $okContent = new OkContent('foo', ['foo-group']);

        $this->mainNormalizer->normalize('foo', Argument::any(), ['groups' => ['foo-group']])->willReturn('foo normalized')->shouldBeCalled();

        $res = $this->okContentNormalizer->normalize($okContent);

        $this->assertEquals('foo normalized', $res);
    }
}
