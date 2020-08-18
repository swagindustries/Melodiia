<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Bridge\Symfony\Form;

use SwagIndustries\Melodiia\Bridge\Symfony\Form\Type\ApiType;
use Symfony\Component\Form\AbstractType as BaseType;

abstract class AbstractType extends BaseType
{
    public function getParent()
    {
        return ApiType::class;
    }
}
