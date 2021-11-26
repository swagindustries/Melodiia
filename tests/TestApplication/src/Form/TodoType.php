<?php

declare(strict_types=1);

namespace TestApplication\Form;

use SwagIndustries\Melodiia\Form\AbstractType;
use SwagIndustries\Melodiia\Form\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use TestApplication\Entity\Todo;

class TodoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextType::class, ['constraints' => new NotBlank()])
            ->add('publishDate', DateTimeType::class, [
                'constraints' => [new GreaterThan('today'), new NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', Todo::class);
    }
}
