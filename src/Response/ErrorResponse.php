<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

use SwagIndustries\Melodiia\Response\Model\UserDataError;

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
