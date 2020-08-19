<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Documentation\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Documentation\Controller\SwaggerUiController;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SwaggerUiControllerTest extends TestCase
{
    /** @var SwaggerUiController */
    private $controller;

    /** @var Environment|ObjectProphecy */
    private $templating;

    public function setUp()
    {
        $this->templating = $this->prophesize(Environment::class);
    }

    public function testItGeneratesAResponseUsingTemplate()
    {
        $attributes = new ParameterBag();
        $attributes->set(SwaggerUiController::PATH_TO_OPEN_API_FILE_OPTION, __DIR__ . '/../../../fixtures/doc.yaml');
        $this->templating->render(Argument::type('string'), ['json' => '{"openapi":"3.0.1"}'])->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $request->attributes = $attributes;
        $this->controller = new SwaggerUiController($this->templating->reveal());

        $this->assertInstanceOf(Response::class, $this->controller->__invoke($request->reveal()));
    }
}
