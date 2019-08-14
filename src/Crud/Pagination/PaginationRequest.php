<?php

declare(strict_types=1);

namespace Biig\Melodiia\Crud\Pagination;

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

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }
}
