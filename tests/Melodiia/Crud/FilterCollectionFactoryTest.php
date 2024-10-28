<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Crud\Controller;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use SwagIndustries\Melodiia\Crud\FilterCollection;
use SwagIndustries\Melodiia\Crud\FilterCollectionFactory;
use SwagIndustries\Melodiia\Crud\FilterCollectionFactoryInterface;
use SwagIndustries\Melodiia\Crud\FilterInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FilterCollectionFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var FormFactoryInterface|ObjectProphecy */
    private $formFactory;

    public function setUp(): void
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
    }

    public function testItCreatesCollection()
    {
        $subject = new FilterCollectionFactory($this->formFactory->reveal(), [new class implements FilterInterface {
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
