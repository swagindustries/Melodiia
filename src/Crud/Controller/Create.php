<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\CrudableModelInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Exception\MelodiiaLogicException;
use Biig\Melodiia\Response\Created;
use Biig\Melodiia\Response\WrongDataInput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Zend\Json\Json;

/**
 * Crud controller that create data model with the data from the request using a form.
 */
final class Create
{
    public const EVENT_PRE_CREATE = 'melodiia.crud.pre_create';
    public const EVENT_POST_CREATE = 'melodiia.crud.post_create';

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(DataStoreInterface $dataStore, FormFactoryInterface $formFactory, EventDispatcherInterface $dispatcher)
    {
        $this->dataStore = $dataStore;
        $this->formFactory = $formFactory;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(Request $request)
    {
        // Metadata you can specify in routing definition
        $modelClass = $request->attributes->get('melodiia_model');
        $form = $request->attributes->get('melodiia_form');

        if (empty($modelClass) || !class_exists($modelClass) || !is_subclass_of($modelClass, CrudableModelInterface::class)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }
        if (empty($form) || !class_exists($form)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }

        $form = $this->formFactory->createNamed('', $form);
        $inputData = Json::decode($request->getContent(), Json::TYPE_ARRAY);
        $form->submit($inputData);

        if (!$form->isSubmitted()) {
            return new WrongDataInput();
        }

        if (!$form->isValid()) {
            return new FormErrorResponse($form);
        }
        $data = $form->getData();
        $this->dispatcher->dispatch(self::EVENT_PRE_CREATE, new CrudEvent($data));

        $this->dataStore->save($data);
        $this->dispatcher->dispatch(self::EVENT_POST_CREATE, $event = new CustomResponseEvent($data));

        if ($event->hasCustomResponse()) {
            return $event->getResponse();
        }

        return new Created($data->getId());
    }
}
