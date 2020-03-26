<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Exception\MelodiiaLogicException;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\OkContent;
use Biig\Melodiia\Response\WrongDataInput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Crud controller that update data model with the data from the request using a form.
 */
final class Update extends BaseCrudController
{
    use CrudControllerTrait;

    public const EVENT_PRE_UPDATE = 'melodiia.crud.pre_update';
    public const EVENT_POST_UPDATE = 'melodiia.crud.post_update';

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var AuthorizationCheckerInterface */
    private $checker;

    public function __construct(DataStoreInterface $dataStore, FormFactoryInterface $formFactory, EventDispatcherInterface $dispatcher, AuthorizationCheckerInterface $checker)
    {
        $this->dataStore = $dataStore;
        $this->formFactory = $formFactory;
        $this->dispatcher = $dispatcher;
        $this->checker = $checker;
    }

    public function __invoke(Request $request, $id): ApiResponse
    {
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $form = $request->attributes->get(self::FORM_ATTRIBUTE);
        $securityCheck = $request->attributes->get(self::SECURITY_CHECK, null);
        $clearMissing = $request->attributes->getBoolean(self::FORM_CLEAR_MISSING, null);

        $this->assertModelClassInvalid($modelClass);

        $data = $this->dataStore->find($modelClass, $id);

        if ($securityCheck && !$this->checker->isGranted($securityCheck, $data)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', $modelClass));
        }

        if (empty($form) || !class_exists($form)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }

        $formOrResponse = $this->decodeInputData($this->formFactory, $form, $request, $clearMissing);
        if ($formOrResponse instanceof ApiResponse) {
            return $formOrResponse;
        }
        $form = $formOrResponse;

        $data = $form->getData();
        $this->dispatcher->dispatch(self::EVENT_PRE_UPDATE, new CrudEvent($data));

        $this->dataStore->save($data);
        $this->dispatcher->dispatch(self::EVENT_POST_UPDATE, $event = new CustomResponseEvent($data));

        if ($event->hasCustomResponse()) {
            return $event->getResponse();
        }

        return new OkContent(['id' => $data->getId()]);
    }
}
