<?php

namespace Biig\Melodiia\Test\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\Controller\Update;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\Ok;
use Biig\Melodiia\Test\TestFixtures\FakeMelodiiaFormType;
use Biig\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateTest extends TestCase
{
    /** @var FormFactoryInterface|ObjectProphecy */
    private $formFactory;

    /** @var FormInterface|ObjectProphecy */
    private $form;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var ParameterBag|ObjectProphecy */
    private $attributes;

    /** @var DataStoreInterface|ObjectProphecy */
    private $dataStore;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    /** @var AuthorizationCheckerInterface|ObjectProphecy */
    private $checker;

    /** @var Update */
    private $controller;

    public function setUp()
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->form = $this->prophesize(FormInterface::class);
        $this->dataStore = $this->prophesize(DataStoreInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->request = $this->prophesize(Request::class);
        $this->checker = $this->prophesize(AuthorizationCheckerInterface::class);

        $this->attributes = $this->prophesize(ParameterBag::class);
        $this->attributes->get(CrudControllerInterface::MODEL_ATTRIBUTE)->willReturn(FakeMelodiiaModel::class);
        $this->attributes->get(CrudControllerInterface::FORM_ATTRIBUTE)->willReturn(FakeMelodiiaFormType::class);
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn(null);
        $this->request->attributes = $this->attributes->reveal();
        $this->request->getContent()->willReturn('{"awesome":"json"}');
        $this->form->submit(['awesome' => 'json'])->willReturn();
        $this->formFactory->createNamed('', Argument::cetera())->willReturn($this->form);

        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass());

        $this->controller = new Update(
            $this->dataStore->reveal(),
            $this->formFactory->reveal(),
            $this->dispatcher->reveal(),
            $this->checker->reveal()
        );
    }

    public function testItReturn400OnNotSubmittedForm()
    {
        $this->form->isSubmitted()->willReturn(false);

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal(), 'id');

        $this->assertInstanceOf(ApiResponse::class, $res);
        $this->assertEquals(400, $res->httpStatus());
    }

    public function testItReturn400OnInvalidForm()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(false);

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal(), 'id');

        $this->assertInstanceOf(FormErrorResponse::class, $res);
        $this->assertEquals(400, $res->httpStatus());
    }

    public function testItCreateMelodiiaObject()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->dispatcher->dispatch(Update::EVENT_PRE_UPDATE, Argument::type(CrudEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(Update::EVENT_POST_UPDATE, Argument::type(CustomResponseEvent::class))->shouldBeCalled();
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal(), 'id');

        $this->assertInstanceOf(Ok::class, $res);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testItThrowAccessDeniedInCaseOfNonGrantedAccess()
    {
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('edit');
        $this->checker->isGranted(Argument::cetera())->willReturn(false);

        ($this->controller)($this->request->reveal(), 'id');
    }
}
