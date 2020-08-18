<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

class Ok extends AbstractApiResponse
{
    public function httpStatus(): int
    {
        return 200;
    }
}
