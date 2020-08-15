<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Crud\Pagination;

class PaginationRequest
{
    /** @var int */
    private $page;

    /** @var int */
    private $maxPerPage;

    public function __construct(int $page, int $maxPerPage)
    {
        $this->page = $page;
        $this->maxPerPage = $maxPerPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }
}
