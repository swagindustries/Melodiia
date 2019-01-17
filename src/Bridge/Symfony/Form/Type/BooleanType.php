<?php

namespace Biig\Melodiia\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Alias CheckboxType, in API context it makes more sense.
 */
class BooleanType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }
}
