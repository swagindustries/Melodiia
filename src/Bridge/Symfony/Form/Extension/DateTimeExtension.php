<?php

namespace Biig\Melodiia\Bridge\Symfony\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Set defaults values for DateTimeType that fits better to APIs.
 */
class DateTimeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'widget' => 'single_text',
                'format' => DateTimeType::HTML5_FORMAT,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     * BC Layer: To be remove in Symfony 5.0.
     */
    public function getExtendedType()
    {
        return DateTimeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [DateTimeType::class];
    }
}
