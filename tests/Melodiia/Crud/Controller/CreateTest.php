<?php

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use SwagIndustries\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use SwagIndustries\Melodiia\Crud\Controller\Create;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\Event\CrudEvent;
use SwagIndustries\Melodiia\Crud\Event\CustomResponseEvent;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\Created;
use SwagIndustries\Melodiia\Test\MockDispatcherTrait;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaFormType;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CreateTest extends TestCase
{
    use MockDispatcherTrait;

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

    /** @var Create */
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
        $this->attributes->getBoolean(CrudControllerInterface::FORM_CLEAR_MISSING, true)->willReturn(true);
        $this->request->attributes = $this->attributes->reveal();
        $this->request->getContent()->willReturn('{"awesome":"json"}');
        $this->form->submit(['awesome' => 'json'], true)->willReturn();
        $this->formFactory->createNamed('', Argument::cetera())->willReturn($this->form);

        $this->controller = new Create(
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
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(ApiResponse::class, $res);
        $this->assertEquals(400, $res->httpStatus());
    }

    public function testItReturn400OnInvalidForm()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(false);

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(FormErrorResponse::class, $res);
        $this->assertEquals(400, $res->httpStatus());
    }

    /**
     * Issue #28.
     */
    public function testItReturnProperlyOnWrongInput()
    {
        $this->request->getContent()->willReturn('{"awesome":json"}'); // Wrong JSON

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal(), 'id');
        $this->assertInstanceOf(ApiResponse::class, $res);
        $this->assertEquals(400, $res->httpStatus());
    }

    public function testItCreateMelodiiaObject()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Create::EVENT_PRE_CREATE)->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CustomResponseEvent::class), Create::EVENT_POST_CREATE)->shouldBeCalled();
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(Created::class, $res);
        $this->assertEquals(201, $res->httpStatus());
    }

    public function testICanChangeTheClearSubmitParam()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Create::EVENT_PRE_CREATE)->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CustomResponseEvent::class), Create::EVENT_POST_CREATE)->shouldBeCalled();
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();
        $this->attributes->getBoolean(CrudControllerInterface::FORM_CLEAR_MISSING, true)->willReturn(false);
        $this->form->submit(['awesome' => 'json'], false)->willReturn()->shouldBeCalled();
        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(Created::class, $res);
        $this->assertEquals(201, $res->httpStatus());
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testItThrowAccessDeniedInCaseOfNonGrantedAccess()
    {
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('edit');
        $this->checker->isGranted(Argument::cetera())->willReturn(false);

        ($this->controller)($this->request->reveal());
    }
}
