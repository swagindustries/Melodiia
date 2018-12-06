<?php

namespace Biig\Happii\Documentation;


use Nekland\Tools\StringTools;
use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\PathItem;
use OpenApi\Context;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class OpenApiDocFactory
 *
 * Feel free to decorate this class. Do not extend it.
 */
final class OpenApiDocFactory implements DocumentationFactoryInterface
{
    private $requestStack;
    private $config;

    public function __construct(RequestStack $requestStack, $config)
    {
        $this->requestStack = $requestStack;
        $this->config = $config;
    }

    public function createOpenApiAnalysis(): Analysis
    {
        $config = [
            //'_context' => new Context(['generated' => true]),
            'info' => new Info([
                //'_context' => new Context(['generated' => true]),
                'title' => $this->config['title'],
                'version' => $this->config['version']
            ]),
            'paths' => [],
            'servers' => [
                ['url' => $this->getApiPath()]
            ]
        ];

        $analysis = new Analysis();

        $openApi = new OpenApi($config);
        $analysis->addAnnotation($openApi, null);

        return $analysis;
    }

    private function getApiPath()
    {
        $path = $this->config['basePath'];
        if (!StringTools::startsWith($path, '/')) {
            $path = '/' . $path;
        }

        return $this->requestStack->getMasterRequest()->getHttpHost() . $path;
    }
}
