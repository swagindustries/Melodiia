<?php

namespace Biig\Melodiia\Test\Bridge\Symfony;

use Biig\Melodiia\Bridge\Symfony\MelodiiaConfiguration;
use Biig\Melodiia\MelodiiaConfigurationInterface;
use PHPUnit\Framework\TestCase;

class MelodiiaConfigurationTest extends TestCase
{
    public function testItImplementsMelodiaConfigurationInterface()
    {
        $this->assertInstanceOf(MelodiiaConfigurationInterface::class, new MelodiiaConfiguration([]));
    }

    public function testItReturnsConfigurationForDocumentation()
    {
        $config = new MelodiiaConfiguration($this->provideConfig());

        $this->assertEquals([
            'main' => ['base_path' => '/api/v1', 'paths' => []],
            'internal_stuff' => ['base_path' => '/internal/v1', 'paths' => []],
        ], $config->getDocumentationConfig());
    }

    public function testItReturnsEndpoints()
    {
        $config = new MelodiiaConfiguration($this->provideConfig());

        $this->assertEquals(['/api/v1', '/internal/v1', '/moar-api-please'], $config->getApiEndpoints());
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
