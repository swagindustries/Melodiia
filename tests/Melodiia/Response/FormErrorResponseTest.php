<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Response;

use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\Exception\InvalidResponseException;
use SwagIndustries\Melodiia\Response\FormErrorResponse;
use SwagIndustries\Melodiia\Response\Model\UserDataError;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Validation;

class FormErrorResponseTest extends TestCase
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    public function setUp(): void
    {
        $validator = Validation::createValidator();
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($validator))
            ->getFormFactory()
        ;
    }

    public function testItFailsIfFormNotSubmitted()
    {
        $this->expectException(InvalidResponseException::class);
        $form = $this->formFactory->createNamedBuilder('')->add('foo')->getForm();
        new FormErrorResponse($form);
    }

    public function testItFailsIfFormHasNoError()
    {
        $this->expectException(InvalidResponseException::class);
        $form = $this->formFactory->createNamedBuilder('')->add('foo')->getForm();
        $form->submit(['foo' => 'hello']);
        new FormErrorResponse($form);
    }

    public function testItTransformFormErrorToArrayOfUserDataError()
    {
        $form = $this->formFactory
            ->createNamedBuilder('')
            ->add('foo', TextType::class, ['constraints' => new NotBlank()])
            ->getForm()
        ;
        $form->submit(['foo' => '']);

        $formErrorResponse = new FormErrorResponse($form);
        $this->assertIsArray($errors = $formErrorResponse->getErrors());
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(UserDataError::class, $errors['foo']);
        $this->assertEquals('foo', $errors['foo']->getPropertyPath());
        $this->assertIsArray($errors['foo']->getErrors());
        $this->assertIsString($errors['foo']->getErrors()[0]);
    }

    public function testManyErrorForOneFormField()
    {
        $form = $this->formFactory
            ->createNamedBuilder('')
            ->add('foo', TextType::class, ['constraints' => [new Email(), new Length(['min' => 10])]])
            ->getForm()
        ;
        $form->submit(['foo' => 'bar']);

        $formErrorResponse = new FormErrorResponse($form);
        $errors = $formErrorResponse->getErrors();

        $this->assertCount(2, $errors['foo']->getErrors());
    }

    public function testNestedFormErrors()
    {
        $form = $this->formFactory
            ->createNamedBuilder('')
            ->add('foo', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('bar', DummyBazFormType::class, ['constraints' => new Valid()])
            ->getForm()
        ;
        $form->submit(['foo' => '', 'bar' => ['baz' => '']]);

        $formErrorResponse = new FormErrorResponse($form);
        $errors = $formErrorResponse->getErrors();
        $this->assertCount(1, $errors['foo']->getErrors());
        $this->assertCount(1, $errors['bar.baz']->getErrors());
    }

    public function testItSupportsNullReason()
    {
        $form = $this->formFactory
            ->createNamedBuilder('')
            ->add('foo', TextType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $event->getForm()->get('foo')->addError(new FormError('some error'));
            })
            ->getForm()
        ;
        $form->submit(['foo' => '']);

        $formErrorResponse = new FormErrorResponse($form);
        $errors = $formErrorResponse->getErrors();
        $this->assertCount(1, $errors['foo']->getErrors());
        $this->assertEquals('some error', $errors['foo']->getErrors()[0]);
    }

    public function testItSupportsStringReason()
    {
        $form = $this->formFactory
            ->createNamedBuilder('')
            ->add('foo', TextType::class)
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $event->getForm()->get('foo')->addError(new FormError('some error', null, [], null, 'Cause'));
            })
            ->getForm()
        ;
        $form->submit(['foo' => '']);

        $formErrorResponse = new FormErrorResponse($form);
        $errors = $formErrorResponse->getErrors();
        $this->assertCount(1, $errors['foo']->getErrors());
        $this->assertEquals('Cause', $errors['foo']->getErrors()[0]);
    }

    public function testCollectionFormErrors()
    {
        $form = $this->formFactory
            ->createNamedBuilder('')
            ->add('foo', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'constraints' => new NotBlank(),
                ],
                'allow_add' => true,
            ])
            ->getForm()
        ;

        $form->submit(['foo' => ['', '']]);

        $formErrorResponse = new FormErrorResponse($form);
        $errors = $formErrorResponse->getErrors();

        $this->assertCount(1, $errors['foo[0]']->getErrors());
        $this->assertCount(1, $errors['foo[1]']->getErrors());
    }

    public function testItReturnRightPropertyPathForFormWithObject()
    {
        $form = $this->formFactory
            ->createNamedBuilder('', FormType::class, new DummyData())
            ->add('foo', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('bar', DummyOtherDummyFormType::class, ['constraints' => [new Valid()]])
            ->getForm()
        ;

        $form->submit(['foo' => '', 'bar' => ['baz' => null]]);

        $formErrorResponse = new FormErrorResponse($form);
        $errors = $formErrorResponse->getErrors();
        $this->assertCount(1, $errors['foo']->getErrors());
        $this->assertCount(1, $errors['bar.baz']->getErrors());
    }
}

class DummyBazFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('baz', TextType::class, ['constraints' => new NotBlank()]);
    }
}

class DummyOtherDummyFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('baz', TextType::class, ['constraints' => new NotBlank()]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', OtherDummy::class);
    }
}

class DummyData
{
    /** @var string */
    private $foo;
    /** @var OtherDummy */
    private $bar;

    public function getFoo(): ?string
    {
        return $this->foo;
    }

    public function setFoo(?string $foo = null): void
    {
        $this->foo = $foo;
    }

    /**
     * @return string
     */
    public function getBar(): ?OtherDummy
    {
        return $this->bar;
    }

    public function setBar(?OtherDummy $bar = null): void
    {
        $this->bar = $bar;
    }
}

class OtherDummy
{
    /** @var string */
    private $baz;

    public function getBaz(): ?string
    {
        return $this->baz;
    }

    public function setBaz(?string $baz = null): void
    {
        $this->baz = $baz;
    }
}
