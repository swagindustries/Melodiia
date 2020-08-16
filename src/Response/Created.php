<?php

namespace SwagIndustries\Melodiia\Response;

class Created implements ApiResponse
{
    /** @var string */
    private $id;

    /** @var string|null */
    private $resourceId;

    public function __construct(string $id, string $resourceId = null)
    {
        $this->id = $id;
        $this->resourceId = $resourceId;
    }

    public function getId(): string
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
}
