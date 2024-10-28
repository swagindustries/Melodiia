<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

class OkNoContent implements ApiResponse
{
    public function httpStatus(): int
    {
        return 204;
    }

    public function headers(): array
    {
        return [];
    }
}
