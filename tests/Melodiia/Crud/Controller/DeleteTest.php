<?php

namespace Biig\Melodiia\Test\Crud\Controller;


use Biig\Melodiia\Crud\Controller\Delete;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\Ok;
use Biig\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DeleteTest extends TestCase
{
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

        $this->controller = new Delete(
            $this->dataStore->reveal(),
            $this->checker->reveal(),
            $this->dispatcher->reveal()
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testItThrow404IfNonExistingItem()
    {
        $this->dataStore->find(Argument::any(), 'id')->willReturn(null);

        ($this->controller)($this->request->reveal(), 'id');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testItDoesNotAllowIllegalAccess()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass());
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('edit');
        $this->checker->isGranted(Argument::cetera())->willReturn(false);

        ($this->controller)($this->request->reveal(), 'id');
    }

    public function testItDeletesUsingDataStore()
    {
        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new FakeMelodiiaModel());
        $this->dispatcher->dispatch(Delete::EVENT_PRE_DELETE, Argument::type(CrudEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(Delete::EVENT_POST_DELETE, Argument::type(CustomResponseEvent::class))->shouldBeCalled();
        $this->dataStore->remove(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal(), 'id');

        $this->assertInstanceOf(Ok::class, $res);
    }
}
