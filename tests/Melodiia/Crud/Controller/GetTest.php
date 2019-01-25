<?php

namespace Biig\Melodiia\Test\Crud\Controller;

use Biig\Melodiia\Crud\Controller\Get;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\NotFound;
use Biig\Melodiia\Response\OkContent;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class GetTest extends TestCase
{
    /** @var DataStoreInterface|ObjectProphecy */
    private $dataStore;

    /** @var Get */
    private $controller;

    public function setUp()
    {
        $this->dataStore = $this->prophesize(DataStoreInterface::class);
        $this->controller = new Get($this->dataStore->reveal());
    }

    public function testItIsIntanceOfMelodiiaController()
    {
        $this->assertInstanceOf(CrudControllerInterface::class, $this->controller);
    }

    public function testItReturnResourceFromDataStoreInsideOkContent()
    {
        $this->dataStore->find('foo', 'id')->willReturn(new \stdClass())->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn('foo');
        $attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $request->attributes = $attributes->reveal();

        $res = ($this->controller)($request->reveal(), 'id');

        $this->assertInstanceOf(OkContent::class, $res);
        $this->assertInstanceOf(\stdClass::class, $res->getContent());
    }

    public function testItReturnNotFoundResponseInCaseOfNoResultFromDataStore()
    {
        $this->dataStore->find('foo', 'id')->willReturn(null)->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn('foo');
        $attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $request->attributes = $attributes->reveal();

        $res = ($this->controller)($request->reveal(), 'id');

        $this->assertInstanceOf(NotFound::class, $res);
    }
}
