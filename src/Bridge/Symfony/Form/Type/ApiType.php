<?php

namespace Biig\Melodiia\Bridge\Symfony\Form\Type;

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
        if ($options['value_object']) {
            $builder->setDataMapper($this->dataMapper);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'value_object' => false,

            /*
             * Creates data object just like standard form would do
             * but used constructor with given data.
             *
             * @param FormInterface $form
             * @return null|object
             * @throws \ReflectionException
             */
            'empty_data' => function (FormInterface $form) {
                return $this->dataMapper->createObject($form);
            },
        ]);
    }
}
