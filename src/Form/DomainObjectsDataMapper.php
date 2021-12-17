<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form;

use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Add support for objects that require constructor instantiation.
 *
 * @internal use DomainObjectsDataMapper instead
 */
class DomainObjectsDataMapperBase extends DataMapper implements DomainObjectDataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function createObject(iterable $form, string $dataClass = null)
    {
        if (null === $dataClass && $form instanceof FormInterface) {
            $dataClass = $form->getConfig()->getOption('data_class');
        }

        $form = iterator_to_array($form);

        if (null === $dataClass || !class_exists($dataClass)) {
            return null;
        }

        $ref = new \ReflectionClass($dataClass);

        $constructor = $ref->getConstructor();
        if (null === $constructor) {
            return new $dataClass();
        }

        $constructorParameters = $ref->getConstructor()->getParameters();

        // Case of anemic object, we have nothing to do here.
        if (count($constructorParameters) < 1) {
            return new $dataClass();
        }

        $constructorData = [];
        foreach ($constructorParameters as $parameter) {
            if (isset($form[$parameter->getName()])) {
                if (null === $form[$parameter->getName()]->getData() && !$parameter->allowsNull() && !$parameter->isDefaultValueAvailable()) {
                    return null;
                }

                $data = $form[$parameter->getName()]->getData();
            } else {
                $data = null;
            }

            if (empty($data) && $parameter->isDefaultValueAvailable()) {
                $data = $parameter->getDefaultValue();
            }

            $constructorData[] = $data;
        }

        return new $dataClass(...$constructorData);
    }
}

if (version_compare(Kernel::VERSION, '6.0', '>=')) {
    class DomainObjectsDataMapper extends DomainObjectsDataMapperBase
    {
        public function mapFormsToData(\Traversable $forms, mixed &$data): void
        {
            $data = $this->createObject($forms, !empty($data) && is_object($data) ? get_class($data) : null);
            parent::mapFormsToData($forms, $data);
        }
    }
} else {
    // BC Layer for PHP 7.4
    // Because mixed type is not supported!
    class DomainObjectsDataMapper extends DomainObjectsDataMapperBase
    {
        public function mapFormsToData(iterable $forms, &$data): void
        {
            $data = $this->createObject($forms, !empty($data) && is_object($data) ? get_class($data) : null);
            parent::mapFormsToData($forms, $data);
        }
    }
}
