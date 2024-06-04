<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Documentation\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Documentation\Controller\SwaggerUiController;
use SwagIndustries\Melodiia\MelodiiaConfiguration;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class SwaggerUiControllerTest extends TestCase
{
    use ProphecyTrait;

    /** @var SwaggerUiController */
    private $controller;

    /** @var Environment|ObjectProphecy */
    private $templating;

    public function setUp(): void
    {
        $this->templating = $this->prophesize(Environment::class);
    }

    public function testItGeneratesAResponseUsingTemplate()
    {
        $request = $this->prophesize(Request::class);
        $request->attributes = new ParameterBag();
        $request = $request->reveal();
        /** @var MelodiiaConfigurationInterface|ObjectProphecy $config */
        $config = $this->prophesize(MelodiiaConfigurationInterface::class);
        $config->getApiConfigFor($request)->willReturn([
            MelodiiaConfiguration::CONFIGURATION_OPENAPI_PATH => __DIR__ . '/../../../fixtures/doc.yaml',
        ]);

        $this->templating->render(Argument::type('string'), ['json' => '{"openapi":"3.0.1"}'])->shouldBeCalled();

        $this->controller = new SwaggerUiController($this->templating->reveal(), $config->reveal());

        $this->assertInstanceOf(Response::class, $this->controller->__invoke($request));
    }
}
