<?php

namespace BiiG\Happii\Test\Documentation\Controller;

use Biig\Happii\Documentation\Controller\OpenApiController;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class OpenApiControllerTest extends TestCase
{
    /** @var OpenApiController */
    private $controller;

    /** @var \Twig_Environment|ObjectProphecy */
    private $templating;

    /** @var RouterInterface|ObjectProphecy */
    private $router;

    public function setUp()
    {
        $this->templating = $this->prophesize(\Twig_Environment::class);
        $this->router = $this->prophesize(RouterInterface::class);
    }

    public function testItGeneratesAResponseUsingTemplate()
    {
        $this->templating->render(Argument::type('string'), ['url_to_json' => '/route/to/json?foo=bar&hello=world'])->shouldBeCalled();
        $this->router->generate('foobar_json', Argument::cetera())->willReturn('/route/to/json');
        $request = $this->prophesize(Request::class);
        $request->get('_route')->willReturn('foobar_html');
        $request->query = [
            'foo' => 'bar',
            'hello' => 'world',
        ];
        $this->controller = new OpenApiController($this->templating->reveal(), $this->router->reveal());

        $this->assertInstanceOf(Response::class, $this->controller->__invoke($request->reveal()));
    }
}
