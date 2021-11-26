<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Form\Type;

use SwagIndustries\Melodiia\Form\Listener\ReorderDataToMatchCollectionListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $resizeListener = new ResizeFormListener(
            $options['entry_type'],
            $options['entry_options'],
            $options['allow_add'],
            $options['allow_delete'],
            $options['delete_empty']
        );

        $reorderInputDataListener = new ReorderDataToMatchCollectionListener();

        $builder->addEventSubscriber($resizeListener);
        $builder->addEventSubscriber($reorderInputDataListener);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => TextType::class,
            'entry_options' => [],
            'delete_empty' => false,
        ]);

        $resolver->setAllowedTypes('delete_empty', ['bool', 'callable']);
    }
}
