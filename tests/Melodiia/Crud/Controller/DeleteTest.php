<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Crud\Controller\Delete;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\Event\CrudEvent;
use SwagIndustries\Melodiia\Crud\Event\CustomResponseEvent;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Crud\Tools\IdResolverInterface;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\Ok;
use SwagIndustries\Melodiia\Test\MockDispatcherTrait;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DeleteTest extends TestCase
{
    use MockDispatcherTrait;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var DataStoreInterface|ObjectProphecy */
    private $dataStore;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    /** @var AuthorizationCheckerInterface|ObjectProphecy */
    private $checker;

    /** @var ParameterBag|ObjectProphecy */
    private $attributes;

    /** @var Delete */
    private $controller;

    protected function setUp()
    {
        $this->request = $this->prophesize(Request::class);
        $this->dataStore = $this->prophesize(DataStoreInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->checker = $this->prophesize(AuthorizationCheckerInterface::class);

        $this->attributes = $this->prophesize(ParameterBag::class);
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn(null);
        $this->attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $this->request->attributes = $this->attributes->reveal();

        $idResolver = $this->prophesize(IdResolverInterface::class);
        $idResolver->resolveId(Argument::type(Request::class), Argument::type('string'))->willReturn('id');

        $this->controller = new Delete(
            $this->dataStore->reveal(),
            $this->dispatcher->reveal(),
            $idResolver->reveal(),
            $this->checker->reveal()
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testItThrow404IfNonExistingItem()
    {
        $this->dataStore->find(Argument::any(), 'id')->willReturn(null);

        ($this->controller)($this->request->reveal());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testItDoesNotAllowIllegalAccess()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass());
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('edit');
        $this->checker->isGranted(Argument::cetera())->willReturn(false);

        ($this->controller)($this->request->reveal());
    }

    public function testItDeletesUsingDataStore()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new FakeMelodiiaModel());
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Delete::EVENT_PRE_DELETE)->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CustomResponseEvent::class), Delete::EVENT_POST_DELETE)->shouldBeCalled();
        $this->dataStore->remove(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(Ok::class, $res);
    }
}
