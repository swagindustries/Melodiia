<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Crud\CrudableModelInterface;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\Event\CrudEvent;
use Biig\Melodiia\Crud\Event\CustomResponseEvent;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Exception\MelodiiaLogicException;
use Biig\Melodiia\Response\Ok;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class Delete implements CrudControllerInterface
{
    public const EVENT_PRE_DELETE = 'melodiia.crud.pre_delete';
    public const EVENT_POST_DELETE = 'melodiia.crud.post_delete';

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var AuthorizationCheckerInterface */
    private $checker;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(DataStoreInterface $dataStore, AuthorizationCheckerInterface $checker, EventDispatcherInterface $dispatcher)
    {
        $this->dataStore = $dataStore;
        $this->checker = $checker;
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(Request $request, $id)
    {
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $securityCheck = $request->attributes->get(self::SECURITY_CHECK, null);

        if (empty($modelClass) || !class_exists($modelClass) || !is_subclass_of($modelClass, CrudableModelInterface::class)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }

        $data = $this->dataStore->find($modelClass, $id);

        if ($data === null) {
            throw new NotFoundHttpException('Cannot find related resource');
        }

        if ($securityCheck && !$this->checker->isGranted($securityCheck, $data)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', $modelClass));
        }

        $this->dispatcher->dispatch(self::EVENT_PRE_DELETE, new CrudEvent($data));
        $this->dataStore->remove($data);
        $this->dispatcher->dispatch(self::EVENT_POST_DELETE, $event = new CustomResponseEvent($data));

        if ($event->hasCustomResponse()) {
            return $event->getResponse();
        }

        return new Ok('Deletion ok');
    }
}
