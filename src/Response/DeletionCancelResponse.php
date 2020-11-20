<?php

namespace SwagIndustries\Melodiia\Response;

class DeletionCancelResponse extends AbstractApiResponse
{
    protected $httpStatus;

    public function __construct($httpStatus, $message = null)
    {
        parent::__construct($message ?? 'Ressource is not deleted.');
        $this->httpStatus = $httpStatus;
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }
}
