<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

class NotFound extends AbstractApiResponse
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message);
    }

    public function httpStatus(): int
    {
        return 404;
    }
}
