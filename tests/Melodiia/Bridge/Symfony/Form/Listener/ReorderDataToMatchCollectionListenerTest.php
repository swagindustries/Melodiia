<?php

namespace Biig\Melodiia\Test\Bridge\Symfony\Form\Listener;

use Biig\Melodiia\Bridge\Symfony\Form\Listener\ReorderDataToMatchCollectionListener;
use Biig\Melodiia\Crud\CrudableModelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class ReorderDataToMatchCollectionListenerTest extends FormIntegrationTestCase
{
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = (new FormFactoryBuilder())->getFormFactory();
        $this->subject = new ReorderDataToMatchCollectionListener();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->factory = null;
        $this->subject = null;
    }

    protected function getBuilder($name = 'name', $data = null)
    {
        $options = ['data' => $data];
        if (!empty($data)) {
            $options['compound'] = true;
        }
        return new FormBuilder($name, null, new EventDispatcher(), $this->factory, $options);
    }

    protected function getForm($name = 'name', $data = null)
    {
        $builder = $this->getBuilder($name, $data);
        foreach ($data as $name => $item) {
            $form = $this->getBuilder($name, $item)
                ->add(0, TextType::class)
                ->add(1, TextType::class)
                ->getForm()
            ;
            $builder->add($name, $form);
        }
        return $builder->getForm();
    }

    public function testItReorderDataInputWithFormData()
    {
        $formData = [
            new class extends \ArrayObject implements CrudableModelInterface {
                public function __construct()
                {
                    parent::__construct(['hello' => 'yo', 'world' => 'ye']);
                }

                public $hello = 'yo';
                public $world = 'ye';
                public function getId()
                {
                    return 'foo';
                }
            },
            new class extends \ArrayObject  implements CrudableModelInterface {
                public function __construct()
                {
                    parent::__construct(['hello' => 'yoh', 'world' => 'yeh']);
                }
                public $hello = 'yoh';
                public $world = 'yeh';
                public function getId()
                {
                    return 'bar';
                }
            }
        ];
        $form = $this->factory->createNamed('', FormType::class, $formData)
            ->add(0, HelloWorldDummyType::class)
            ->add(1, HelloWorldDummyType::class)
        ;
        $data = [['hello' => 'baz'], ['id' => 'bar', 'hello' => 'bar'], ['id' => 'foo', 'hello' => 'foo']];
        $event = new PreSubmitEvent($form, $data);
        $this->subject->preSubmit($event);

        // The listener will remove ids
        // it will order known items and add new at the end
        $this->assertEquals(
            [['hello' => 'foo'], ['hello' => 'bar'],['hello' => 'baz']],
            $event->getData()
        );
    }

    public function testItRemovesDataThatDoesNotExistsAnymore()
    {
        $formData = [
            new class extends \ArrayObject implements CrudableModelInterface {
                public function __construct()
                {
                    parent::__construct(['hello' => 'yo', 'world' => 'ye']);
                }

                public $hello = 'yo';
                public $world = 'ye';
                public function getId()
                {
                    return 'foo';
                }
            },
        ];
        $form = $this->factory->createNamed('', FormType::class, $formData)
            ->add(0, HelloWorldDummyType::class)
        ;
        $data = [['hello' => 'bar']];
        $event = new PreSubmitEvent($form, $data);
        $this->subject->preSubmit($event);

        // Will remove known foo (indexed by 0) and add new bar
        $this->assertEquals(
            [1 => ['hello' => 'bar']],
            $event->getData()
        );
    }

    public function testItDoesNothingOnEmptyData()
    {
        $event = $this->prophesize(FormEvent::class);
        $event->getData()->willReturn(null);
        $event->getForm()->shouldNotBeCalled();
        $this->subject->preSubmit($event->reveal());
    }
}

class HelloWorldDummyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hello')->add('world');
    }
}
