<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Serialization\Json;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Response\Model\Collection;
use SwagIndustries\Melodiia\Response\OkContent;
use SwagIndustries\Melodiia\Serialization\Json\OkContentNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class OkContentNormalizerTest extends TestCase
{
    /** @var NormalizerInterface|ObjectProphecy */
    private $mainNormalizer;

    /** @var RequestStack|ObjectProphecy */
    private $requestStack;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var OkContentNormalizer */
    private $okContentNormalizer;

    public function setUp(): void
    {
        $this->mainNormalizer = $this->prophesize(Serializer::class);
        $this->requestStack = $this->prophesize(RequestStack::class);
        $this->request = $this->prophesize(Request::class);
        $this->requestStack->getMainRequest()->willReturn($this->request->reveal());

        $this->okContentNormalizer = new OkContentNormalizer($this->requestStack->reveal());
        $this->okContentNormalizer->setSerializer($this->mainNormalizer->reveal());
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

    public function testItSerializeArray()
    {
        $okContent = new OkContent(['foo']);

        $this->mainNormalizer->normalize(['foo'], Argument::cetera())->willReturn('foo normalized')->shouldBeCalled();

        $res = $this->okContentNormalizer->normalize($okContent);

        $this->assertEquals('foo normalized', $res);
    }

    public function testItSerializePager()
    {
        // 11 words
        $content = [
            'foo', 'bar', 'baz', 'hello', 'world', 'more', 'content', 'yaya', 'yoyo', 'random', 'words',
        ];
        $pager = new Pagerfanta(new ArrayAdapter($content));
        $pager->setMaxPerPage(4);
        $pager->setCurrentPage(2);
        $okContent = new OkContent($pager);
        $this->request->getUri()->willReturn('http://foo.com/bar?page=2');

        foreach (['world', 'more', 'content', 'yaya'] as $data) {
            $this->mainNormalizer->normalize($data, Argument::cetera())->willReturn($data . ' normalized')->shouldBeCalled();
        }

        $res = $this->okContentNormalizer->normalize($okContent);

        $this->assertEquals([
            'data' => [
                'world normalized', 'more normalized', 'content normalized', 'yaya normalized',
            ],
            'meta' => [
                'totalPages' => 3,
                'totalResults' => 11,
                'currentPage' => 2,
                'maxPerPage' => 4,
            ],
            'links' => [
                'prev' => 'http://foo.com/bar?page=1',
                'next' => 'http://foo.com/bar?page=3',
                'last' => 'http://foo.com/bar?page=3',
                'first' => 'http://foo.com/bar?page=1',
            ],
        ], $res);
    }

    public function testItSerializeCollection()
    {
        $collection = new Collection([['acme' => 'foo'], ['acme' => 'bar']]);
        $this->mainNormalizer->normalize(['acme' => 'foo'], Argument::cetera())->willReturn(['acme' => 'foo'])->shouldBeCalled();
        $this->mainNormalizer->normalize(['acme' => 'bar'], Argument::cetera())->willReturn(['acme' => 'bar'])->shouldBeCalled();
        $this->request->getUri()->willReturn('http://foo.com/bar');

        $res = $this->okContentNormalizer->normalize(new OkContent($collection));

        $this->assertEquals([
            'data' => [
                ['acme' => 'foo'],
                ['acme' => 'bar'],
            ],
            'meta' => [
                'totalPages' => 1,
                'totalResults' => 2,
                'currentPage' => 1,
                'maxPerPage' => 2,
            ],
            'links' => [
                'prev' => null,
                'next' => null,
                'last' => 'http://foo.com/bar',
                'first' => 'http://foo.com/bar',
            ],
        ], $res);
    }
}
