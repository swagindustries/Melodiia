<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Crud\CrudableModelInterface;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\FilterInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Exception\MelodiiaLogicException;
use Biig\Melodiia\Response\OkContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GetAll implements CrudControllerInterface
{
    /** @var DataStoreInterface */
    private $dataStore;

    /** @var AuthorizationCheckerInterface */
    private $checker;

    /** @var FilterInterface */
    private $filters;

    public function __construct(DataStoreInterface $dataStore, AuthorizationCheckerInterface $checker, array $filters = [])
    {
        $this->dataStore = $dataStore;
        $this->checker = $checker;
        $this->filters = $filters;
    }

    public function __invoke(Request $request)
    {
        // Metadata you can specify in routing definition
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $securityCheck = $request->attributes->get(self::SECURITY_CHECK, null);
        $groups = $request->attributes->get(self::SERIALIZATION_GROUP, []);

        if (empty($modelClass) || !class_exists($modelClass) || !is_subclass_of($modelClass, CrudableModelInterface::class)) {
            throw new MelodiiaLogicException('If you use melodiia CRUD classes, you need to specify a model.');
        }

        if ($securityCheck && !$this->checker->isGranted($securityCheck)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', $modelClass));
        }

        $page = $request->query->getInt('page', 1);
        $maxPerPage = $request->attributes->get(self::MAX_PER_PAGE_ATTRIBUTE, 30);

        $items = $this->dataStore->getPaginated($modelClass, $page, $maxPerPage, $this->filters);

        return new OkContent($items, $groups);
    }
}
