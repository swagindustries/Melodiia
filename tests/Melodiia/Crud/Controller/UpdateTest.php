<?php

namespace Biig\Melodiia\Test\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\Controller\Update;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Crud\Tools\IdResolverInterface;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\OkContent;
use Biig\Melodiia\Test\MockDispatcherTrait;
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
        $this->attributes->has(CrudControllerInterface::FORM_CLEAR_MISSING)->willReturn(false);
        $this->attributes->getBoolean(CrudControllerInterface::FORM_CLEAR_MISSING, false)->willReturn(false);
        $this->request->attributes = $this->attributes->reveal();
        $this->request->getMethod()->willReturn('POST');
        $this->request->getContent()->willReturn('{"awesome":"json"}');
        $this->form->submit(['awesome' => 'json'], false)->willReturn();
        $this->formFactory->createNamed('', Argument::cetera())->willReturn($this->form);

        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass());


        $idResolver = $this->prophesize(IdResolverInterface::class);
        $idResolver->resolveId(Argument::type(Request::class), Argument::type('string'))->willReturn('id');

        $this->controller = new Update(
            $this->dataStore->reveal(),
            $this->formFactory->reveal(),
            $this->dispatcher->reveal(),
            $this->checker->reveal(),
            $idResolver->reveal()
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

    /**
     * Issue #54
     */
    public function testItClearMissingWhileNullGivenAndMethodPatch()
    {
        // Mocking
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);
        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Argument::type('string'));

        // Most important check
        $this->form->submit(['awesome' => 'json'], true)->willReturn()->shouldBeCalled();

        $this->request->getMethod()->willReturn('PATCH');

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(ApiResponse::class, $res);
        $this->assertEquals(200, $res->httpStatus());
    }

    public function testItClearMissingWhenEnforcedToTrue()
    {
        // Mocking
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);
        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->attributes->has(CrudControllerInterface::FORM_CLEAR_MISSING)->willReturn(true);
        $this->attributes->getBoolean(CrudControllerInterface::FORM_CLEAR_MISSING)->willReturn(true);
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Argument::type('string'));

        // Most important check
        $this->form->submit(['awesome' => 'json'], true)->willReturn()->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());
        $this->assertInstanceOf(ApiResponse::class, $res);
        $this->assertEquals(200, $res->httpStatus());
    }

    /**
     * Issue #28
     */
    public function testItReturnProperlyOnWrongInput()
    {
        $this->request->getContent()->willReturn('{"awesome":json"}'); // Wrong JSON

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

    public function testItUpdateMelodiiaObject()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Update::EVENT_PRE_UPDATE)->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CustomResponseEvent::class), Update::EVENT_POST_UPDATE)->shouldBeCalled();
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(OkContent::class, $res);
    }

    public function testICanChangeClearMissingOption()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->attributes->has(CrudControllerInterface::FORM_CLEAR_MISSING)->willReturn(true);
        $this->attributes->getBoolean(CrudControllerInterface::FORM_CLEAR_MISSING)->willReturn(true);
        $this->form->submit(['awesome' => 'json'], true)->willReturn()->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CrudEvent::class), Update::EVENT_PRE_UPDATE)->shouldBeCalled();
        $this->mockDispatch($this->dispatcher, Argument::type(CustomResponseEvent::class), Update::EVENT_POST_UPDATE)->shouldBeCalled();
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(OkContent::class, $res);
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
