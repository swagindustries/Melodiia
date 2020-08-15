<?php

namespace SwagIndustries\Melodiia\Bridge\Symfony\Form\Listener;

use SwagIndustries\Melodiia\Crud\CrudableModelInterface;
use SwagIndustries\Melodiia\Exception\MelodiiaLogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ReorderDataToMatchCollectionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            // (PRE SUBMIT of ResizeFormListener must comes after)
            FormEvents::PRE_SUBMIT => ['preSubmit', 10],
        ];
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data)) {
            return;
        }

        $form = $event->getForm();
        $dataOrdered = [];

        // We need to know the last index of the collection.
        // We cannot do incremental index for data because the last index may be deleted.
        $lastCollectionIndex = 0;
        foreach ($form as $name => $child) {
            $lastCollectionIndex = $name;
            $item = $child->getData();
            if (!$item instanceof CrudableModelInterface) {
                throw new MelodiiaLogicException(sprintf('Impossible manage the model of type "%s" inside Melodiia because it does not implement CrudableModelInterface.', get_class($item)));
            }
            $itemId = (string) $item->getId();
            foreach ($data as $inputItem) {
                if (!isset($inputItem['id'])) {
                    continue;
                }
                if ($inputItem['id'] === $itemId) {
                    $dataOrdered[$name] = $inputItem;
                    break; // This break removes duplicated items (based on id) inside input data
                }
            }
        }

        // Add "new" items (without id) to the data ordered
        foreach ($data as $inputItem) {
            if (!isset($inputItem['id'])) {
                ++$lastCollectionIndex;
                $dataOrdered[$lastCollectionIndex] = $inputItem;
            }
        }

        // Removing ids, they are just here for this listener and are not part of the form.
        foreach ($dataOrdered as $index => $item) {
            if (isset($dataOrdered[$index]['id'])) {
                unset($dataOrdered[$index]['id']);
            }
        }

        $event->setData($dataOrdered);
    }
}
