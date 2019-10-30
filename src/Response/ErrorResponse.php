<?php

namespace Biig\Melodiia\Response;

use Biig\Melodiia\Response\AbstractUserDataErrorResponse;
use Biig\Melodiia\Response\Model\UserDataError;

class ErrorResponse extends AbstractUserDataErrorResponse
{
    /** @var UserDataError[] */
    protected $errors;

    public function __construct(array $errors, string $errorKey = '')
    {
        $userError = new UserDataError($errorKey, []);
        foreach ($errors as $error) {
            $userError->addError($error);
        }

        $this->errors = [$userError];
    }

    /**
     * @return UserDataError[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
