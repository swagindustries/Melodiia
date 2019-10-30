<?php

namespace Biig\Melodiia\Bridge\Symfony\Response;

use Biig\Melodiia\Response\AbstractUserDataErrorResponse;
use Biig\Melodiia\Response\Model\UserDataError;

class ErrorResponse extends AbstractUserDataErrorResponse
{
    /** @var UserDataError[] */
    protected $errors;

    /** @var string */
    private $errorKey;

    public function __construct(array $errors, string $errorKey = '')
    {
        $this->errorKey = $errorKey;

        $userError = new UserDataError($this->errorKey, []);
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
