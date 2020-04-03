<?php

namespace Biig\Melodiia\Test\Crud\Tools;

use Biig\Melodiia\Crud\Tools\IdResolverInterface;
use Biig\Melodiia\Crud\Tools\SimpleIdResolver;
use Biig\Melodiia\Exception\IdMissingException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class SimpleIdResolverTest extends TestCase
{
    private $subject;

    protected function setUp()
    {
        $this->subject = new SimpleIdResolver();
    }

    protected function tearDown()
    {
        $this->subject = null;
    }

    public function testItIsInstanceOfIdResolverInterface()
    {
        $this->assertInstanceOf(IdResolverInterface::class, $this->subject);
    }

    public function testItGetsStandardIdIfAvailable()
    {
        $request = $this->getRequest(['id' => 'foo']);

        $this->assertEquals('foo', $this->subject->resolveId($request, SimpleIdFakeModel::class));
    }

    public function testItGetsSpecificToModelId()
    {
        $request = $this->getRequest(['simpleIdFakeModelId' => 'yolo']);

        $this->assertEquals('yolo', $this->subject->resolveId($request, SimpleIdFakeModel::class));
    }

    public function testItThrowsIfImpossibleToFindId()
    {
        $this->expectException(IdMissingException::class);
        $this->subject->resolveId($this->getRequest([]), SimpleIdFakeModel::class);
    }

    private function getRequest(array $attributes): Request
    {
        $request = $this->prophesize(Request::class);
        $request->attributes = new ParameterBag($attributes);

        return $request->reveal();
    }
}

class SimpleIdFakeModel
{
}
