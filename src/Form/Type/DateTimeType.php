<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as OriginalDateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'widget' => 'single_text',
                'format' => OriginalDateTimeType::HTML5_FORMAT,
                'input' => 'datetime_immutable',
            ])
        ;
    }

    public function getParent()
    {
        return OriginalDateTimeType::class;
    }
}
