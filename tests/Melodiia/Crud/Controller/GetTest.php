<?php

namespace Biig\Melodiia\Test\Crud\Controller;

use Biig\Melodiia\Crud\Controller\Get;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\NotFound;
use Biig\Melodiia\Response\OkContent;
use Biig\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GetTest extends TestCase
{
    /** @var DataStoreInterface|ObjectProphecy */
    private $dataStore;

    /** @var AuthorizationCheckerInterface|ObjectProphecy */
    private $authorizationChecker;

    /** @var Get */
    private $controller;

    public function setUp()
    {
        $this->dataStore = $this->prophesize(DataStoreInterface::class);
        $this->authorizationChecker = $this->prophesize(AuthorizationCheckerInterface::class);
        $this->controller = new Get($this->dataStore->reveal(), $this->authorizationChecker->reveal());
    }

    public function testItIsIntanceOfMelodiiaController()
    {
        $this->assertInstanceOf(CrudControllerInterface::class, $this->controller);
    }

    public function testItReturnResourceFromDataStoreInsideOkContent()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass())->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn(null);
        $request->attributes = $attributes->reveal();

        $res = ($this->controller)($request->reveal(), 'id');

        $this->assertInstanceOf(OkContent::class, $res);
        $this->assertInstanceOf(\stdClass::class, $res->getContent());
    }

    public function testItReturnNotFoundResponseInCaseOfNoResultFromDataStore()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(null)->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn(null);
        $request->attributes = $attributes->reveal();

        $res = ($this->controller)($request->reveal(), 'id');

        $this->assertInstanceOf(NotFound::class, $res);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testItCheckAccessToResourceIfSpecifiedInConfiguration()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass())->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('view');
        $request->attributes = $attributes->reveal();

        $this->authorizationChecker->isGranted('view', Argument::any())->willReturn(false);

        ($this->controller)($request->reveal(), 'id');
    }

    public function testItCheckAccessAndSuccessIfAuthorized()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass())->shouldBeCalled();
        $request = $this->prophesize(Request::class);
        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $attributes->get(CrudControllerInterface::SERIALIZATION_GROUP, [])->willReturn([]);
        $attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('view');
        $request->attributes = $attributes->reveal();

        $this->authorizationChecker->isGranted('view', Argument::any())->willReturn(true);

        $res = ($this->controller)($request->reveal(), 'id');

        $this->assertInstanceOf(OkContent::class, $res);
        $this->assertInstanceOf(\stdClass::class, $res->getContent());
    }
}
