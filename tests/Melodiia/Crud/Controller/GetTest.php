<?php

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Crud\Controller\Get;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Crud\Tools\IdResolverInterface;
use SwagIndustries\Melodiia\Response\NotFound;
use SwagIndustries\Melodiia\Response\OkContent;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
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

        $idResolver = $this->prophesize(IdResolverInterface::class);
        $idResolver->resolveId(Argument::type(Request::class), Argument::type('string'))->willReturn('id');

        $this->controller = new Get($this->dataStore->reveal(), $this->authorizationChecker->reveal(), $idResolver->reveal());
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

        $res = ($this->controller)($request->reveal());

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

        $res = ($this->controller)($request->reveal());

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

        ($this->controller)($request->reveal());
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

        $res = ($this->controller)($request->reveal());

        $this->assertInstanceOf(OkContent::class, $res);
        $this->assertInstanceOf(\stdClass::class, $res->getContent());
    }
}
