<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Bridge\Symfony\Routing;

use Nekland\Utils\Tempfile\TemporaryDirectory;
use OpenApi\Analysis;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Bridge\Symfony\Routing\CrudDocumentationFactory;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Documentation\Controller\OpenApiJsonController;
use SwagIndustries\Melodiia\Documentation\DocumentationFactoryInterface;
use SwagIndustries\Melodiia\Documentation\OpenApiDocFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;

class CrudDocumentationFactoryTest extends TestCase
{
    /** @var DocumentationFactoryInterface|ObjectProphecy */
    private $decorated;

    /** @var Router|ObjectProphecy */
    private $router;

    /** @var CrudDocumentationFactory */
    private $factory;

    public function setUp()
    {
        $this->decorated = $this->prophesize(DocumentationFactoryInterface::class);
        $this->router = $this->prophesize(Router::class);
        $this->factory = new CrudDocumentationFactory($this->decorated->reveal(), $this->router->reveal(), '/api/v1');
    }

    public function testItIsInstanceOfDocumentationFactoryInterface()
    {
        $this->assertInstanceOf(DocumentationFactoryInterface::class, $this->factory);
    }

    public function testItDoesNothingIfNoControllerFound()
    {
        $route = new Route('/foo');
        $routeCollection = new RouteCollection();
        $routeCollection->add('foo', $route);

        $this->router->getRouteCollection()->willReturn($routeCollection);
        $analysis = $this->prophesize(Analysis::class);
        $analysis->addAnnotation()->shouldNotBeCalled();
        $this->decorated->createOpenApiAnalysis()->willReturn($analysis->reveal());

        $this->assertNotNull($this->factory->createOpenApiAnalysis());
    }

    public function testItGenerateValidOpenApiDoc()
    {
        // Mocks
        $request = $this->prophesize(Request::class);
        $request->getSchemeAndHttpHost()->willReturn('http://localhost:9999');
        $requestStack = $this->prophesize(RequestStack::class);
        $requestStack->getMasterRequest()->willReturn($request->reveal());

        $route1 = new Route(
            '/foo',
            [
                '_controller' => 'melodiia.crud.controller.get',
                CrudControllerInterface::MODEL_ATTRIBUTE => FakeModel::class,
                CrudDocumentationFactory::DOCUMENTATION_SUMMARY => 'Get a fake model',
            ],
            [], [], '', [], ['GET']
        );
        $route2 = new Route(
            '/bar',
            [
                '_controller' => 'melodiia.crud.controller.create',
                CrudControllerInterface::MODEL_ATTRIBUTE => FakeModel::class,
            ],
            [], [], '', [], ['POST']
        );
        $collection = new RouteCollection();
        $collection->add('route1', $route1);
        $collection->add('route2', $route2);

        $this->router->getRouteCollection()->willReturn($collection);

        $tmpDir = new TemporaryDirectory();

        // Real objects
        $decorated = new OpenApiDocFactory($requestStack->reveal(), ['base_path' => '/api/v1/', 'title' => 'Awesome API', 'version' => '1.0.0', 'description' => 'Awesome API is Awesome']);
        $factory = new CrudDocumentationFactory($decorated, $this->router->reveal(), '/api/v1/');
        $controller = new OpenApiJsonController([$tmpDir->getPathname()], $factory);

        // Test
        $response = $controller();
        $json = $response->getContent();

        // OpenApi format check
        $json = json_decode($json);
        $validator = new \JsonSchema\Validator();
        $validator->validate(
            $json,
            (object) ['$ref' => 'file://' . realpath(__DIR__ . '/../../../../openapi3-schema.json')]
        );
        // Get errors with $validator->getErrors()
        $this->assertTrue($validator->isValid());

        $tmpDir->remove();
    }

    public function testItIgnoreRouteWhenDisabledSpecified()
    {
        $route = new Route('/foo', [
            CrudDocumentationFactory::DOCUMENTATION_DISABLED => true,
            '_controller' => 'melodiia.crud.controller.get',
            CrudControllerInterface::MODEL_ATTRIBUTE => FakeModel::class, ]
        );
        $routeCollection = new RouteCollection();
        $routeCollection->add('foo', $route);

        $this->router->getRouteCollection()->willReturn($routeCollection);
        $analysis = $this->prophesize(Analysis::class);
        $analysis->addAnnotation(Argument::cetera())->shouldNotBeCalled();
        $this->decorated->createOpenApiAnalysis()->willReturn($analysis->reveal());

        $this->assertNotNull($this->factory->createOpenApiAnalysis());
    }
}

class FakeModel
{
    private $foo;

    /**
     * FakeModel constructor.
     *
     * @param $foo
     */
    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return mixed
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param mixed $foo
     */
    public function setFoo($foo): void
    {
        $this->foo = $foo;
    }
}
