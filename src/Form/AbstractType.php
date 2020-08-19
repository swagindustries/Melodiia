<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form;

use SwagIndustries\Melodiia\Form\Type\ApiType;
use Symfony\Component\Form\AbstractType as BaseType;

abstract class AbstractType extends BaseType
{
    public function getParent()
    {
        return ApiType::class;
    }
}
