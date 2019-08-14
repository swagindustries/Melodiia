<?php

declare(strict_types=1);

namespace Biig\Melodiia\Crud\Pagination;

use Symfony\Component\HttpFoundation\Request;

interface PaginationRequestFactoryInterface
{
    /**
     * This method should extract needed information from the request to build a PaginationRequest.
     *
     * @param Request $request
     *
     * @return PaginationRequest
     */
    public function createPaginationRequest(Request $request): PaginationRequest;
}
