<?php

namespace Biig\Melodiia\Bridge\Symfony\Form\Type;

use Biig\Melodiia\Bridge\Symfony\Form\DomainObjectDataMapperInterface;
use Biig\Melodiia\Bridge\Symfony\Form\DomainObjectsDataMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiType extends AbstractType
{
    /** @var DataMapperInterface */
    private $dataMapper;

    public function __construct(DataMapperInterface $dataMapper = null)
    {
        $this->dataMapper = $dataMapper ?? new DomainObjectsDataMapper();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataMapper = $this->dataMapper;

        if (null !== $options['customDataMapper']) {
            $dataMapper = $options['customDataMapper'];
        }

        if ($options['value_object']) {
            $builder->setDataMapper($dataMapper);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'value_object' => false,
            'customDataMapper' => null,

            /*
             * Creates data object just like standard form would do
             * but used constructor with given data.
             *
             * @param FormInterface $form
             * @return null|object
             * @throws \ReflectionException
             */
            'empty_data' => function (FormInterface $form) {
                $dataMapper = $this->dataMapper;
                if (null !== $form->getConfig()->getOption('customDataMapper')) {
                    $dataMapper = $form->getConfig()->getOption('customDataMapper');
                }

                return $dataMapper->createObject($form);
            },
        ]);

        $resolver->setAllowedTypes('customDataMapper', ['null', DomainObjectDataMapperInterface::class]);
    }
}
