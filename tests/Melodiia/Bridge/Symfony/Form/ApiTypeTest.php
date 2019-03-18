<?php

namespace Biig\Melodiia\Test\Bridge\Symfony\Form;

use Biig\Melodiia\Bridge\Symfony\Form\Type\ApiType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiTypeTest extends FormIntegrationTestCase
{
    public function testItIsASymfonyFormType()
    {
        $type = new ApiType();

        $this->assertInstanceOf(AbstractType::class, $type);
    }

    public function testItReturnsNullForEmptyDataInCaseOfNoDataAvailable()
    {
        $form = $this->factory->createNamed('', FakeTypeUsingApiType::class);
        $this->assertEquals(null, $form->getData());
        $this->assertInstanceOf(FormView::class, $form->createView());
    }

    public function testItReturnsDataForEmptyDataInCaseOfDataAvailable()
    {
        $form = $this->factory->createNamed('', FakeTypeUsingApiType::class);
        $form->submit(['foo' => 'some content']);
        $data = $form->getData();
        $this->assertInstanceOf(FakeModel::class, $data);
        $this->assertEquals('some content', $data->getFoo());
    }

    protected function getTypes()
    {
        return [new ApiType()];
    }
}

class FakeModel
{
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}

class FakeTypeUsingApiType extends \Biig\Melodiia\Bridge\Symfony\Form\AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('foo');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => FakeModel::class, 'value_object' => true]);
    }
}
