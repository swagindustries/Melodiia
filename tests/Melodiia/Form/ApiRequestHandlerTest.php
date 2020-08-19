<?php

declare(strict_types=1);

namespace Melodiia\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SwagIndustries\Melodiia\Form\ApiRequestHandler;
use SwagIndustries\Melodiia\Form\Type\ApiType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ApiRequestHandlerTest extends TestCase
{
    /** @var ApiRequestHandler */
    private $subject;

    /** @var \Prophecy\Prophecy\ObjectProphecy|FormInterface */
    private $form;

    /** @var \Prophecy\Prophecy\ObjectProphecy|FormConfigInterface */
    private $formConfig;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Request */
    private $request;

    public function setUp(): void
    {
        $this->subject = new ApiRequestHandler();

        $this->request = $this->prophesize(Request::class);
        $this->form = $this->prophesize(FormInterface::class);
        $this->formConfig = $this->prophesize(FormConfigInterface::class);

        $this->form->getConfig()->willReturn($this->formConfig->reveal());
        $this->request->getMethod()->willReturn('POST');
    }

    public function tearDown()
    {
        $this->subject = null;
        $this->form = null;
        $this->formConfig = null;
        $this->request = null;
    }

    public function testItSubmitJsonData()
    {
        $this->form->submit(['hello' => 'foo'], false)->shouldBeCalled();
        $this->request->getContent()->willReturn('{"hello":"foo"}');

        $this->subject->handleRequest($this->form->reveal(), $this->request->reveal());
    }

    public function testItAddsAFormErrorInCaseOfWrongInput()
    {
        $this->form->submit(null, false)->shouldBeCalled();
        $this->form->addError(Argument::type(FormError::class))->shouldBeCalled();
        $this->request->getContent()->willReturn('{"hello":"foo}');

        $this->subject->handleRequest($this->form->reveal(), $this->request->reveal());
    }

    public function testItClearsMissingOnPatchRequest()
    {
        $this->form->submit(['hello' => 'foo'], true)->shouldBeCalled();
        $this->request->getContent()->willReturn('{"hello":"foo"}');
        $this->request->getMethod()->willReturn('PATCH');

        $this->subject->handleRequest($this->form->reveal(), $this->request->reveal());
    }

    public function testICanChangeClearMissingOption()
    {
        $this->form->submit(['hello' => 'foo'], true)->shouldBeCalled();
        $this->formConfig->getOption(ApiType::CLEAR_MISSING_OPTION)->willReturn(true);
        $this->request->getContent()->willReturn('{"hello":"foo"}');

        $this->subject->handleRequest($this->form->reveal(), $this->request->reveal());
    }

    public function testItSupportsGetRequests()
    {
        $query = new ParameterBag(['hello' => 'foo']);
        $this->form->submit(['hello' => 'foo'])->shouldBeCalled();
        $this->request->getMethod()->willReturn('GET');
        $this->request->query = $query;

        $this->subject->handleRequest($this->form->reveal(), $this->request->reveal());
    }
}
