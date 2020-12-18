<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test;

use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\MelodiiaConfiguration;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class MelodiiaConfigurationTest extends TestCase
{
    /** @var RouterInterface */
    private $router;

    /** @var MelodiiaConfiguration */
    private $subject;

    protected function setUp(): void
    {
        $this->router = $this->prophesize(RouterInterface::class);
        $this->subject = new MelodiiaConfiguration($this->provideConfig(), $this->router->reveal());
    }

    public function testItImplementsMelodiaConfigurationInterface()
    {
        $this->assertInstanceOf(MelodiiaConfigurationInterface::class, $this->subject);
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
                    'base_path' => '/api/v1',
                    'pagination' => [
                        'max_per_page_attribute' => 'size',
                    ],
                ],
                'internal_stuff' => [
                    'base_path' => '/internal/v1',
                ],
                'something_else' => [
                    'base_path' => '/moar-api-please',
                ],
            ],
            'form_extensions' => [
                'datetime' => true,
            ],
        ];
    }
}
