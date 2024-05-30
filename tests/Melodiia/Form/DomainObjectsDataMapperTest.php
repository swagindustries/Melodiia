<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use SwagIndustries\Melodiia\Form\DomainObjectsDataMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigBuilder;

class DomainObjectsDataMapperTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsInstanceOfDataMapper()
    {
        $this->assertInstanceOf(DataMapperInterface::class, new DomainObjectsDataMapper());
    }

    public function testItExtendsPropertyPathDataMapper()
    {
        $this->assertInstanceOf(DataMapper::class, new DomainObjectsDataMapper());
    }

    public function testItBuildObjectFromForm()
    {
        $mapper = new DomainObjectsDataMapper();

        $dispatcher = $this->prophesize(EventDispatcherInterface::class);
        $dispatcher->hasListeners(Argument::cetera())->willReturn(false);
        $form1 = new Form((new FormConfigBuilder('hello', null, $dispatcher->reveal()))->setData('world')->getFormConfig());
        $form2 = new Form((new FormConfigBuilder('foo', null, $dispatcher->reveal()))->setData('bar')->getFormConfig());

        $form = new \ArrayIterator(['hello' => $form1, 'foo' => $form2]);

        $obj = $mapper->createObject($form, FakeValueObject::class);
        $this->assertInstanceOf(FakeValueObject::class, $obj);
    }
}

class FakeValueObject
{
    private $hello;
    private $foo;

    /**
     * FakeValueObject constructor.
     */
    public function __construct($hello, $foo)
    {
        $this->hello = $hello;
        $this->foo = $foo;
    }

    public function getHello()
    {
        return $this->hello;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
