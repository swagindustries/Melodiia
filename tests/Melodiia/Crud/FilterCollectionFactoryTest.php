<?php

namespace Biig\Melodiia\Test\Crud\Controller;

use Biig\Melodiia\Crud\FilterCollection;
use Biig\Melodiia\Crud\FilterCollectionFactory;
use Biig\Melodiia\Crud\FilterCollectionFactoryInterface;
use Biig\Melodiia\Crud\FilterInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterCollectionFactoryTest extends TestCase
{
    /** @var FormFactoryInterface|ObjectProphecy */
    private $formFactory;

    public function setUp()
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
    }

    public function testItCreatesCollection()
    {
        $subject = new FilterCollectionFactory($this->formFactory->reveal(), [new class() implements FilterInterface {
            public function filter($queryBuilder, FormInterface $form): void
            { /* do nothing */
            }

            public function supports(string $class): bool
            {
                return \stdClass::class === $class;
            }

            public function buildForm(FormBuilderInterface $formBuilder): void
            { /* do nothing */
            }
        }]);
        $this->assertInstanceOf(FilterCollection::class, $subject->createCollection(\stdClass::class));
    }

    public function testItIsInstanceOfFilterFactory()
    {
        $subject = new FilterCollectionFactory($this->formFactory->reveal(), []);
        $this->assertInstanceOf(FilterCollectionFactoryInterface::class, $subject);
    }
}
