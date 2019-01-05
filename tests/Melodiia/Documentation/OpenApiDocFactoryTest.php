<?php

namespace Biig\Melodiia\Test\Documentation;

use Biig\Melodiia\Documentation\DocumentationFactoryInterface;
use Biig\Melodiia\Documentation\OpenApiDocFactory;
use OpenApi\Analysis;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class OpenApiDocFactoryTest extends TestCase
{
    /** @var RequestStack|ObjectProphecy */
    private $requestStack;

    public function setUp()
    {
        $this->requestStack = $this->prophesize(RequestStack::class);
    }

    public function testItImplementsDocumentationFactoryInterface()
    {
        $factory = $this->createFactory([]);
        $this->assertInstanceOf(DocumentationFactoryInterface::class, $factory);
    }

    public function testItReturnAnOpenApiAnalysis()
    {
        $factory = $this->createFactory([
            'title' => 'hello',
            'version' => '1.0.0',
            'basePath' => '/foo',
            'description' => null,
        ]);
        $request = $this->prophesize(Request::class);
        $request->getHttpHost()->willReturn('http://localhost');
        $this->requestStack->getMasterRequest()->willReturn($request);
        $this->assertInstanceOf(Analysis::class, $factory->createOpenApiAnalysis());
    }

    private function createFactory(array $config)
    {
        return new OpenApiDocFactory($this->requestStack->reveal(), $config);
    }
}
