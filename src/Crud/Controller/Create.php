<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Exception\MelodiiaLogicException;
use Biig\Melodiia\Response\ApiResponse;
use Biig\Melodiia\Response\Created;
use Biig\Melodiia\Response\WrongDataInput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zend\Json\Json;

/**
 * Crud controller that create data model with the data from the request using a form.
 */
final class Create implements CrudControllerInterface
{
    use CrudControllerTrait;

    public const EVENT_PRE_CREATE = 'melodiia.crud.pre_create';
    public const EVENT_POST_CREATE = 'melodiia.crud.post_create';

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

    public function __invoke(Request $request): ApiResponse
    {
        // Metadata you can specify in routing definition
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $form = $request->attributes->get(self::FORM_ATTRIBUTE);
        $securityCheck = $request->attributes->get(self::SECURITY_CHECK, null);
        $clearMissing = $request->attributes->getBoolean(self::FORM_CLEAR_MISSING, true);

        $this->assertModelClassInvalid($modelClass);

        if ($securityCheck && !$this->checker->isGranted($securityCheck)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', $modelClass));
        }

        if (empty($form) || !class_exists($form)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }

        $form = $this->formFactory->createNamed('', $form);
        $inputData = Json::decode($request->getContent(), Json::TYPE_ARRAY);
        $form->submit($inputData, $clearMissing);

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
