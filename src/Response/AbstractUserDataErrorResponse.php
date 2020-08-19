<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

use SwagIndustries\Melodiia\Response\Model\UserDataError;

abstract class AbstractUserDataErrorResponse implements ApiResponse
{
    /**
     * @return UserDataError[]
     */
    abstract public function getErrors(): array;

    public function httpStatus(): int
    {
        return 400;
    }

    public function headers(): array
    {
        return [];
    }
}
