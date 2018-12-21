<?php

namespace Biig\Happii\Response;

use Biig\Happii\Response\Model\UserDataError;

abstract class AbstractUserDataErrorResponse implements ApiResponse
{
    /**
     * @return UserDataError[]
     */
    public abstract function getErrors(): array;

    public function httpStatus(): int
    {
        return 400;
    }
}
