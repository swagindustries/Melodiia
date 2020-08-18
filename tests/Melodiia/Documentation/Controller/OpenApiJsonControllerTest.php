<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Documentation\Controller;

use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\Documentation\Controller\OpenApiJsonController;
use SwagIndustries\Melodiia\Documentation\DocumentationFactoryInterface;

class OpenApiJsonControllerTest extends TestCase
{
    public function testItGeneratesJsonFromConfig()
    {
        $factory = new DummyFactory();

        $controller = new OpenApiJsonController([
            __DIR__ . '/../../../fixtures/api_endpoint',
        ], $factory);

        $response = $controller();

        $this->assertEquals(json_decode($response->getContent(), true), [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Dummy Awesome Api',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => 'http://localhost/api-swagg'],
            ],
            'paths' => [
                '/callback' => [
                    'get' => [
                        'description' => 'Validate that an email was sent successfully.',
                        'operationId' => 'App\Tests\fixtures\api_endpoint\GetDummyEndpoint::__invoke',
                        'responses' => [
                            '200' => [
                                'description' => 'Everything went good.',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}

class DummyFactory implements DocumentationFactoryInterface
{
    public function createOpenApiAnalysis(): Analysis
    {
        $config = [
            'info' => new Info([
                'title' => 'Dummy Awesome Api',
                'version' => '1.0.0',
            ]),
            'paths' => [],
            'servers' => [
                ['url' => 'http://localhost/api-swagg'],
            ],
        ];

        $analysis = new Analysis();

        $openApi = new OpenApi($config);
        $analysis->addAnnotation($openApi, null);

        return $analysis;
    }
}
