<?php

namespace Biig\Melodiia\Bridge\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,

            /*
             * Creates data object just like standard form would do
             * but used constructor with given data.
             *
             * @param FormInterface $form
             * @return null|object
             * @throws \ReflectionException
             */
            'empty_data' => function (FormInterface $form) {
                $dataClass = $form->getConfig()->getOption('data_class');

                if (!class_exists($dataClass)) {
                    return null;
                }

                $ref = new \ReflectionClass($dataClass);
                $constructorParameters = $ref->getConstructor()->getParameters();

                // Case of anemic model, we have nothing to do here.
                if (count($constructorParameters) < 1) {
                    return new $dataClass();
                }

                $constructorData = [];
                foreach ($constructorParameters as $parameter) {
                    if ($form->has($parameter->getName())) {
                        if (null === $form->get($parameter->getName())->getData() && !$parameter->allowsNull() && !$parameter->isDefaultValueAvailable()) {
                            return null;
                        }

                        $data = $form->get($parameter->getName())->getData();
                    } else {
                        $data = null;
                    }

                    if (empty($data) && $parameter->isDefaultValueAvailable()) {
                        $data = $parameter->getDefaultValue();
                    }

                    $constructorData[] = $data;
                }

                return new $dataClass(...$constructorData);
            },
        ]);
    }
}
