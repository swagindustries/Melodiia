<?php

namespace SwagIndustries\Melodiia\Crud\Controller;

use SwagIndustries\Melodiia\Bridge\Symfony\Response\FormErrorResponse;
use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\Crud\FilterCollectionFactoryInterface;
use SwagIndustries\Melodiia\Crud\Pagination\PaginationRequestFactoryInterface;
use SwagIndustries\Melodiia\Crud\Persistence\DataStoreInterface;
use SwagIndustries\Melodiia\Response\OkContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        FilterCollectionFactoryInterface $collectionFactory,
        PaginationRequestFactoryInterface $pagesRequestFactory,
        AuthorizationCheckerInterface $checker = null
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
        $groups = $request->attributes->get(self::SERIALIZATION_GROUP, []);

        $this->assertModelClassInvalid($modelClass);
        $this->assertResourceRights($request);

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
