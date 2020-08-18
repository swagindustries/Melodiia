<?php

namespace SwagIndustries\Melodiia\Bridge\Symfony\Form\Type;

use SwagIndustries\Melodiia\Bridge\Symfony\Form\DomainObjectDataMapperInterface;
use SwagIndustries\Melodiia\Bridge\Symfony\Form\DomainObjectsDataMapper;
use SwagIndustries\Melodiia\Form\ApiRequestHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiType extends AbstractType
{
    public const CLEAR_MISSING_OPTION = 'clear_missing';

    /** @var DataMapperInterface */
    private $dataMapper;

    public function __construct(DataMapperInterface $dataMapper = null)
    {
        $this->dataMapper = $dataMapper ?? new DomainObjectsDataMapper();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataMapper = $this->dataMapper;

        if (null !== $options['melodiiaDataMapper']) {
            $dataMapper = $options['melodiiaDataMapper'];
        }

        if ($options['value_object']) {
            $builder->setDataMapper($dataMapper);
        }

        $builder->setRequestHandler(new ApiRequestHandler());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'value_object' => false,
            'melodiiaDataMapper' => null,

            // If set to false, all fields will be required, in the request, otherwise some may be removed.
            // By default is null, but it will be automatically guess by the request handler.
            self::CLEAR_MISSING_OPTION => null,

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
                if (null !== $form->getConfig()->getOption('melodiiaDataMapper')) {
                    $dataMapper = $form->getConfig()->getOption('melodiiaDataMapper');
                }

                return $dataMapper->createObject($form);
            },
        ]);

        $resolver->setAllowedTypes('melodiiaDataMapper', ['null', DomainObjectDataMapperInterface::class]);
    }
}
