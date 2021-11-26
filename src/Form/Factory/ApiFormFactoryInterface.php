<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form\Factory;

use SwagIndustries\Melodiia\Form\Type\ApiType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface as FormFactoryInterfaceBase;
use Symfony\Component\Form\FormInterface;

interface ApiFormFactoryInterface extends FormFactoryInterfaceBase
{
    public function createApi(string $type = ApiType::class, $data = null, array $options = []): FormInterface;

    public function createApiBuilder(string $type = ApiType::class, $data = null, array $options = []): FormBuilderInterface;
}
