<?php

namespace Biig\Happii\Documentation\Controller;

use Biig\Happii\Bridge\Symfony\Exception\ConfigException;
use Biig\Happii\Documentation\DocumentationFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SwaggerJsonController
 *
 * This controller is very specific to swagger-php.
 */
class OpenApiJsonController
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var DocumentationFactoryInterface
     */
    private $factory;

    /**
     * DocumentationController constructor.
     *
     * @param string|string[]               $paths   List of path where controller may be located.
     * @param DocumentationFactoryInterface $factory
     *
     * @throws ConfigException
     */
    public function __construct($paths, DocumentationFactoryInterface $factory)
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }
        $this->setPaths($paths);

        $this->factory = $factory;
    }

    public function __invoke(Request $request, $ext = 'html')
    {
        $openApi = \OpenApi\scan($this->paths, ['analysis' => $this->factory->createOpenApiAnalysis()]);

        return new JsonResponse($openApi->toJson(), Response::HTTP_OK, [], true);
    }

    /**
     * @param array $paths
     *
     * @throws ConfigException
     */
    private function setPaths(array $paths)
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                throw new ConfigException('Path "' . $path . '" given as configuration does not exist.');
            }
        }

        $this->paths = $paths;
    }
}
