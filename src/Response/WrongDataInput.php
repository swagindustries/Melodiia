<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

class WrongDataInput extends AbstractApiResponse
{
    public function __construct(string $message = 'Wrong data input')
    {
        parent::__construct($message);
    }

    public function httpStatus(): int
    {
        return 400;
    }
}
