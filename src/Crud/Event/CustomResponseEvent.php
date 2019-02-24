<?php

namespace Biig\Melodiia\Crud\Event;

use Biig\Melodiia\Response\ApiResponse;

class CustomResponseEvent extends CrudEvent
{
    /** @var ApiResponse|null */
    private $response;

    public function setResponse(ApiResponse $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?ApiResponse
    {
        return $this->response;
    }

    public function hasCustomResponse(): bool
    {
        return null !== $this->response;
    }
}
