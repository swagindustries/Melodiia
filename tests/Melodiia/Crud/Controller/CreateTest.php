<?php

namespace Biig\Melodiia\Test\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Form\AbstractType;
use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\Controller\Create;
use Biig\Melodiia\Crud\CrudableModelInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\Created;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class CreateTest extends TestCase
{
    /** @var FormFactoryInterface|ObjectProphecy */
    private $formFactory;

    /** @var FormInterface|ObjectProphecy */
    private $form;

    /** @var Request|ObjectProphecy */
    private $request;

    /** @var DataStoreInterface|ObjectProphecy */
    private $dataStore;

    /** @var EventDispatcherInterface|ObjectProphecy */
    private $dispatcher;

    /** @var Create */
    private $controller;

    public function setUp()
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->form = $this->prophesize(FormInterface::class);
        $this->dataStore = $this->prophesize(DataStoreInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->request = $this->prophesize(Request::class);

        $attributes = $this->prophesize(ParameterBag::class);
        $attributes->get('melodiia_model')->willReturn(FakeMelodiiaModel::class);
        $attributes->get('melodiia_form')->willReturn(FakeMelodiiaFormType::class);
        $this->request->attributes = $attributes->reveal();
        $this->request->getContent()->willReturn('{"awesome":"json"}');
        $this->form->submit(['awesome' => 'json'])->willReturn();
        $this->formFactory->createNamed('', Argument::cetera())->willReturn($this->form);

        $this->controller = new Create($this->dataStore->reveal(), $this->formFactory->reveal(), $this->dispatcher->reveal());
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

    public function testItCreateMelodiiaObject()
    {
        $this->form->isSubmitted()->willReturn(true);
        $this->form->isValid()->willReturn(true);

        $this->form->getData()->willReturn(new FakeMelodiiaModel());
        $this->dispatcher->dispatch(Create::EVENT_PRE_CREATE, Argument::type(CrudEvent::class))->shouldBeCalled();
        $this->dispatcher->dispatch(Create::EVENT_POST_CREATE, Argument::type(CustomResponseEvent::class))->shouldBeCalled();
        $this->dataStore->save(Argument::type(FakeMelodiiaModel::class))->shouldBeCalled();

        /** @var ApiResponse $res */
        $res = ($this->controller)($this->request->reveal());

        $this->assertInstanceOf(Created::class, $res);
        $this->assertEquals(201, $res->httpStatus());
    }
}

class FakeMelodiiaModel implements CrudableModelInterface
{
    public function getId()
    {
        return 1;
    }
}

class FakeMelodiiaFormType extends AbstractType
{
}
