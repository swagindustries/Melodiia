<?php

namespace Biig\Melodiia\Bridge\Symfony\Form;

use Biig\Melodiia\Bridge\Symfony\Form\Type\ApiType;
use Symfony\Component\Form\AbstractType as BaseType;

abstract class AbstractType extends BaseType
{
    public function getParent()
    {
        return ApiType::class;
    }
}
