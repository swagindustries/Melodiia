<?php

namespace Biig\Melodiia\Response;

use Biig\Melodiia\Response\Model\UserDataError;

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
}
