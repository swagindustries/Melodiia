<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Pagination;

use SwagIndustries\Melodiia\Crud\CrudControllerInterface;
use SwagIndustries\Melodiia\MelodiiaConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginationRequestFactory implements PaginationRequestFactoryInterface
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_ITEMS_PER_PAGE = 30;
    public const DEFAULT_MAX_PER_PAGE_ATTRIBUTE = 'max_per_page';

    /** @var MelodiiaConfigurationInterface */
    private $configuration;

    public function __construct(MelodiiaConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function createPaginationRequest(Request $request): PaginationRequest
    {
        $page = $request->query->getInt('page', self::DEFAULT_PAGE);
        $maxPerPage = $request->attributes->getInt(CrudControllerInterface::MAX_PER_PAGE_ATTRIBUTE, self::DEFAULT_ITEMS_PER_PAGE);
        $apiConfig = $this->configuration->getApiConfigFor($request) ?? [];

        if ($request->attributes->getBoolean(CrudControllerInterface::ALLOW_USER_DEFINE_MAX_PAGE, false)) {
            $maxPerPageQueryAttribute = self::DEFAULT_MAX_PER_PAGE_ATTRIBUTE;

            if (isset($apiConfig['pagination']) && isset($apiConfig['pagination']['max_per_page_attribute']) && \is_string($apiConfig['pagination']['max_per_page_attribute'])) {
                $maxPerPageQueryAttribute = $apiConfig['pagination']['max_per_page_attribute'];
            }

            if (0 !== $userMax = $request->query->getInt($maxPerPageQueryAttribute, 0)) {
                $maxPerPage = $userMax;
            }
        }

        if ($maxPerPage > $maxAllowed = $request->attributes->getInt(CrudControllerInterface::MAX_PER_PAGE_ALLOWED, 250)) {
            $maxPerPage = $maxAllowed;
        }

        return new PaginationRequest($page, $maxPerPage);
    }
}
