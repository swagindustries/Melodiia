<?php

namespace SwagIndustries\Melodiia\Response;

class Ok implements ApiResponse
{
    /** @var string */
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function httpStatus(): int
    {
        return 200;
    }
}
