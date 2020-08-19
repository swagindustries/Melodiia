<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

class Created implements ApiResponse
{
    /** @var string|int */
    private $id;

    /** @var string|null */
    private $resourceId;

    public function __construct($id, string $resourceId = null)
    {
        $this->id = $id;
        $this->resourceId = $resourceId;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function httpStatus(): int
    {
        return 201;
    }

    public function headers(): array
    {
        return [];
    }
}
