<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

interface ApiResponse
{
    public function httpStatus(): int;

    public function headers(): array;
}
