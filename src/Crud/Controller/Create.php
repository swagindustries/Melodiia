<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Controller;

use SwagIndustries\Melodiia\Crud\Event\CrudEvent;
use SwagIndustries\Melodiia\Crud\Event\CustomResponseEvent;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Exception\MelodiiaLogicException;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Response\Created;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Crud controller that create data model with the data from the request using a form.
 */
final class Create extends BaseCrudController
{
    use CrudControllerTrait;

    public const EVENT_PRE_CREATE = 'melodiia.crud.pre_create';
    public const EVENT_POST_CREATE = 'melodiia.crud.post_create';

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var AuthorizationCheckerInterface|null */
    private $checker;

    public function __construct(DataStoreInterface $dataStore, FormFactoryInterface $formFactory, EventDispatcherInterface $dispatcher, AuthorizationCheckerInterface $checker = null)
    {
        parent::__construct($dispatcher);
        $this->dataStore = $dataStore;
        $this->formFactory = $formFactory;
        $this->checker = $checker;
    }

    public function __invoke(Request $request): ApiResponse
    {
        // Metadata you can specify in routing definition
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $form = $request->attributes->get(self::FORM_ATTRIBUTE);
        $clearMissing = $request->attributes->getBoolean(self::FORM_CLEAR_MISSING, true);

        $this->assertModelClassInvalid($modelClass);

        $this->assertResourceRights($request);

        if (empty($form) || !class_exists($form)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }

        $formOrResponse = $this->decodeInputData($this->formFactory, $form, $request, $clearMissing);
        if ($formOrResponse instanceof ApiResponse) {
            return $formOrResponse;
        }
        $form = $formOrResponse;

        $data = $form->getData();
        $this->dispatch(new CrudEvent($data), self::EVENT_PRE_CREATE);

        $this->dataStore->save($data);
        $this->dispatch($event = new CustomResponseEvent($data), self::EVENT_POST_CREATE);

        if ($event->hasCustomResponse()) {
            return $event->getResponse();
        }

        return new Created($data->getId());
    }
}
