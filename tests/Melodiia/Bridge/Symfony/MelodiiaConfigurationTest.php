<?php

namespace SwagIndustries\Melodiia\Test\Bridge\Symfony;

use SwagIndustries\Melodiia\Bridge\Symfony\MelodiiaConfiguration;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class MelodiiaConfigurationTest extends TestCase
{
    /** @var RouterInterface */
    private $router;

    /** @var MelodiiaConfiguration */
    private $subject;

    protected function setUp()
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->subject = new MelodiiaConfiguration($this->provideConfig(), $this->router->reveal());
    }

    public function testItImplementsMelodiaConfigurationInterface()
    {
        $this->assertInstanceOf(MelodiiaConfigurationInterface::class, $this->subject);
    }

    public function testItReturnsConfigurationForDocumentation()
    {
        $this->assertEquals([
            'main' => ['base_path' => '/api/v1', 'paths' => []],
            'internal_stuff' => ['base_path' => '/internal/v1', 'paths' => []],
        ], $this->subject->getDocumentationConfig());
    }

    public function testItReturnsEndpoints()
    {
        $this->assertEquals(['/api/v1', '/internal/v1', '/moar-api-please'], $this->subject->getApiEndpoints());
    }

    public function testItReturnNullIfTheGivenRequestIsntUnderMelodiiaApis()
    {
        $request = $this->prophesize(Request::class);
        $request->getPathInfo()->willReturn('/some/invalid/path/info');
        $this->assertNull($this->subject->getApiConfigFor($request->reveal()));
    }

    public function testItReturnTheGoodApiConfigurationIfPathInfoMatch()
    {
        $request = $this->prophesize(Request::class);
        $request->getPathInfo()->willReturn('/api/v1/test/it/work');
        $apiConfig = $this->subject->getApiConfigFor($request->reveal());

        $excepted = $this->provideConfig()['apis']['main'];
        $excepted['name'] = 'main';
        $this->assertEquals($excepted, $apiConfig);
    }

    private function provideConfig()
    {
        return [
            'apis' => [
                'main' => [
                    'title' => 'Biig API',
                    'description' => 'This is huge.',
                    'version' => '1.0.0',
                    'base_path' => '/api/v1',
                    'paths' => [],
                    'enable_doc' => true,
                    'doc_factory' => null,
                    'pagination' => [
                        'max_per_page_attribute' => 'size',
                    ],
                ],
                'internal_stuff' => [
                    'title' => 'Yolo internals',
                    'version' => '1.0.0',
                    'base_path' => '/internal/v1',
                    'paths' => [],
                    'enable_doc' => true,
                    'doc_factory' => null,
                    'description' => null,
                ],
                'something_else' => [
                    'title' => 'Moar internals',
                    'version' => '1.0.0',
                    'base_path' => '/moar-api-please',
                    'paths' => [],
                    'enable_doc' => false,
                    'doc_factory' => null,
                    'description' => null,
                ],
            ],
            'form_extensions' => [
                'datetime' => true,
            ],
        ];
    }
}
