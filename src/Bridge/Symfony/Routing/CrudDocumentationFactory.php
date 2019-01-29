<?php

namespace Biig\Melodiia\Bridge\Symfony\Routing;

use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Documentation\DocumentationFactoryInterface;
use Nekland\Tools\StringTools;
use OpenApi\Analysis;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;

/**
 * Class CrudDocumentationFactory
 *
 * Feel free to extends and redefine some of the methods of the
 */
class CrudDocumentationFactory implements DocumentationFactoryInterface
{
    public const DOCUMENTATION_TAG = 'documentation_tag';
    public const DOCUMENTATION_SUMMARY = 'documentation_summary';
    public const DOCUMENTATION_DESCRIPTION = 'documentation_description';

    /** @var DocumentationFactoryInterface */
    private $decorated;

    /** @var Router */
    private $router;

    /** @var string */
    private $basePath;

    public function __construct(DocumentationFactoryInterface $decoratedFactory, Router $router, string $basePath)
    {
        $this->decorated = $decoratedFactory;
        $this->router = $router;
        $this->basePath = $basePath;
    }

    public function createOpenApiAnalysis(): Analysis
    {
        $analysis = $this->decorated->createOpenApiAnalysis();

        foreach ($this->router->getRouteCollection() as $route) {
            /** @var Route $route */
            $controller = $route->getDefaults()['_controller'] ?? null;
            if ($controller === 'melodiia.crud.controller.get') {
                $analysis->addAnnotation($this->createDocForGet($route), null);
            }
            if ($controller === 'melodiia.crud.controller.create') {
                $analysis->addAnnotation($this->createDocForCreate($route), null);
            }
        }

        return $analysis;
    }

    protected function createDocForGet(Route $route)
    {
        $defaults = $route->getDefaults();
        $model = $this->getModelName($defaults[CrudControllerInterface::MODEL_ATTRIBUTE]);
        $tag = $model;

        if (isset($defaults[self::DOCUMENTATION_TAG])) {
            $tag = $defaults[self::DOCUMENTATION_TAG];
        }

        $annot = new Get([
            'path' => $this->cleanPath($route->getPath()),
            'responses' => [
                new Response([
                    'response' => 200,
                    'description' => 'Resource exists and is returned.',
                ]),
                new Response([
                    'response' => 404,
                    'description' => 'Resource doesn\'t exist.',
                ])
            ],
            'operationId' => $model . ':get',
            'parameters' => [
                new Parameter([
                    'name' => 'id',
                    'in' => 'path',
                    'description' => 'Id of the resource',
                    'required' => true,
                    'ref' => '#ref/components/schemas/Id',
                    'schema' => new Schema([
                        'type' => 'string',
                        'schema' => 'id'
                    ])
                ])
            ],
            'summary' => $defaults[self::DOCUMENTATION_SUMMARY] ?? 'Return a resource',
            'description' => $defaults[self::DOCUMENTATION_DESCRIPTION] ?? 'Return the ressource based on given id',
            'tags' => [$tag]
        ]);

        return $annot;
    }

    public function createDocForCreate(Route $route)
    {
        $defaults = $route->getDefaults();
        $model = $this->getModelName($defaults[CrudControllerInterface::MODEL_ATTRIBUTE]);
        $tag = $model;

        if (isset($defaults[self::DOCUMENTATION_TAG])) {
            $tag = $defaults[self::DOCUMENTATION_TAG];
        }

        $annot = new Post([
            'path' => $this->cleanPath($route->getPath()),
            'responses' => [
                new Response([
                    'response' => 201,
                    'description' => 'Resource created.'
                ]),
                new Response([
                    'response' => 400,
                    'description' => 'Your request don\'t match the validation constraints.',
                ]),
            ],
            'operationId' => $model . ':post',
            'summary' => $defaults[self::DOCUMENTATION_SUMMARY] ?? 'Create a resource',
            'description' => $defaults[self::DOCUMENTATION_DESCRIPTION] ?? 'Return the id of the created resource',
            'tags' => [$tag]
        ]);

        return $annot;
    }

    protected function cleanPath(string $path): string
    {
        return StringTools::removeStart($path, rtrim($this->basePath, '/'));
    }

    protected function getModelName(string $fqcn): string
    {
        $model = explode('\\', $fqcn);
        $model = end($model);

        return $model;
    }

    private static function crudServices()
    {
        return [
            'melodiia.crud.controller.create',
            'melodiia.crud.controller.get'
        ];
    }
}
