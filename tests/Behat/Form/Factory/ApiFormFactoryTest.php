<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Tests\Behat\Form\Factory;

use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\Form\Factory\ApiFormFactory;
use SwagIndustries\Melodiia\Form\Type\ApiType;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;

class ApiFormFactoryTest extends TestCase
{
    public function testThatCreateApiReturnAFormWithoutNameAndApiTypeIsDefault()
    {
        $formFactory = new ApiFormFactory(new FormRegistry([], new ResolvedFormTypeFactory()));

        $form = $formFactory->createApi();
        $this->assertInstanceOf(ApiType::class, $form->getConfig()->getType()->getInnerType());
        $this->assertEquals('', $form->getName());
    }

    public function testThatCreateApiBuilderReturnAFormBuilderWithoutNameAndApiTypeIsDefault()
    {
        $formFactory = new ApiFormFactory(new FormRegistry([], new ResolvedFormTypeFactory()));

        $formBuilder = $formFactory->createApiBuilder();
        $form = $formBuilder->getType();
        $this->assertInstanceOf(ApiType::class, $form->getInnerType());
        $this->assertEquals('', $formBuilder->getName());
    }
}
