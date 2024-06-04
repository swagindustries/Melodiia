<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Controller;

use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\Event\CustomResponseEvent;
use SwagIndustries\Melodiia\Crud\Event\DeleteEvent;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Crud\Tools\IdResolverInterface;
use SwagIndustries\Melodiia\Crud\Tools\SimpleIdResolver;
use SwagIndustries\Melodiia\Exception\IdMissingException;
use SwagIndustries\Melodiia\Response\Ok;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class Delete extends BaseCrudController implements CrudControllerInterface
{
    use CrudControllerTrait;

    public const EVENT_PRE_DELETE = 'melodiia.crud.pre_delete';
    public const EVENT_POST_DELETE = 'melodiia.crud.post_delete';

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var AuthorizationCheckerInterface|null */
    private $checker;

    /** @var IdResolverInterface */
    private $idResolver;

    public function __construct(DataStoreInterface $dataStore, EventDispatcherInterface $dispatcher, ?IdResolverInterface $idResolver = null, ?AuthorizationCheckerInterface $checker = null)
    {
        parent::__construct($dispatcher);
        $this->dataStore = $dataStore;
        $this->idResolver = $idResolver ?? new SimpleIdResolver();
        $this->checker = $checker;
    }

    public function __invoke(Request $request)
    {
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);

        try {
            $id = $this->idResolver->resolveId($request, $modelClass);
        } catch (IdMissingException $e) {
            throw new NotFoundHttpException('No id found', $e);
        }

        $this->assertModelClassInvalid($modelClass);

        $data = $this->dataStore->find($modelClass, $id);

        if (null === $data) {
            throw new NotFoundHttpException(\sprintf('resource item "%s" with id "%s" can not be found', $modelClass, $id));
        }

        $this->assertResourceRights($request, $data);

        $this->dispatch($deleteEvent = new DeleteEvent($data), self::EVENT_PRE_DELETE);

        if (!$deleteEvent->isStopped()) {
            $this->dataStore->remove($data);
        }

        $this->dispatch($event = new CustomResponseEvent($data), self::EVENT_POST_DELETE);

        if ($event->hasCustomResponse()) {
            return $event->getResponse();
        }

        if ($deleteEvent->isStopped()) {
            return $deleteEvent->getDeleteResponse();
        }

        return new Ok('Deletion ok');
    }
}
