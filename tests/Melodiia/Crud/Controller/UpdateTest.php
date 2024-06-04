<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Crud\Controller\Update;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\Event\CrudEvent;
use SwagIndustries\Melodiia\Crud\Event\CustomResponseEvent;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Crud\Tools\IdResolverInterface;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\FormErrorResponse;
use SwagIndustries\Melodiia\Response\OkContent;
use SwagIndustries\Melodiia\Test\MockDispatcherTrait;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaFormType;
use SwagIndustries\Melodiia\Test\TestFixtures\FakeMelodiiaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateTest extends TestCase
{
    use MockDispatcherTrait;
    use ProphecyTrait;

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

    public function setUp(): void
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
        $this->form->handleRequest($this->request)->willReturn($this->form->reveal());
        $this->formFactory->createNamed('', Argument::cetera())->willReturn($this->form);

        $this->dataStore->find(FakeMelodiiaModel::class, 'id')->willReturn(new \stdClass());

        $idResolver = $this->prophesize(IdResolverInterface::class);
        $idResolver->resolveId(Argument::type(Request::class), Argument::type('string'))->willReturn('id');

        $this->controller = new Update(
            $this->dataStore->reveal(),
            $this->formFactory->reveal(),
            $this->dispatcher->reveal(),
            $idResolver->reveal(),
            $this->checker->reveal()
        );
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

    public function testItThrowAccessDeniedInCaseOfNonGrantedAccess()
    {
        $this->expectException(AccessDeniedException::class);
        $this->attributes->get(CrudControllerInterface::SECURITY_CHECK, null)->willReturn('edit');
        $this->checker->isGranted(Argument::cetera())->willReturn(false);

        ($this->controller)($this->request->reveal());
    }
}
