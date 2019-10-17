<?php

namespace Biig\Melodiia\Crud\Controller;

use Biig\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use Biig\Melodiia\Crud\CrudControllerInterface;
use Biig\Melodiia\Crud\FilterCollectionFactoryInterface;
use Biig\Melodiia\Crud\Pagination\PaginationRequestFactoryInterface;
use Biig\Melodiia\Crud\Persistence\DataStoreInterface;
use Biig\Melodiia\Response\OkContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GetAll implements CrudControllerInterface
{
    use CrudControllerTrait;

    /** @var DataStoreInterface */
    private $dataStore;

    /** @var AuthorizationCheckerInterface */
    private $checker;

    /** @var FilterCollectionFactoryInterface */
    private $filterCollectionFactory;

    /** @var PaginationRequestFactoryInterface */
    private $pagesRequestFactory;

    public function __construct(
        DataStoreInterface $dataStore,
        AuthorizationCheckerInterface $checker,
        FilterCollectionFactoryInterface $collectionFactory,
        PaginationRequestFactoryInterface $pagesRequestFactory
    ) {
        $this->dataStore = $dataStore;
        $this->checker = $checker;
        $this->filterCollectionFactory = $collectionFactory;
        $this->pagesRequestFactory = $pagesRequestFactory;
    }

    public function __invoke(Request $request)
    {
        // Metadata you can specify in routing definition
        $modelClass = $request->attributes->get(self::MODEL_ATTRIBUTE);
        $securityCheck = $request->attributes->get(self::SECURITY_CHECK, null);
        $groups = $request->attributes->get(self::SERIALIZATION_GROUP, []);

        $this->assertModelClassInvalid($modelClass);

        if ($securityCheck && !$this->checker->isGranted($securityCheck)) {
            throw new AccessDeniedException(\sprintf('Access denied to data of type "%s".', $modelClass));
        }

        $filters = $this->filterCollectionFactory->createCollection($modelClass);
        $form = $filters->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return new FormErrorResponse($form);
        }

        $pageRequest = $this->pagesRequestFactory->createPaginationRequest($request);
        $items = $this->dataStore->getPaginated($modelClass, $pageRequest->getPage(), $filters, $pageRequest->getMaxPerPage());

        return new OkContent($items, $groups);
    }
}
