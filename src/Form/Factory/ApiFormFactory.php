<?php
declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form\Factory;

use SwagIndustries\Melodiia\Form\Type\ApiType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class ApiFormFactory extends FormFactory implements ApiFormFactoryInterface
{
    public function createApi(string $type = ApiType::class, $data = null, array $options = []): FormInterface
    {
        return $this->createNamed('', $type, $data, $options);
    }

    public function createApiBuilder(string $type = ApiType::class, $data = null, array $options = []): FormBuilderInterface
    {
        return $this->createNamedBuilder('', $type, $data, $options);
    }
}
