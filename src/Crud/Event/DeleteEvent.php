<?php

namespace SwagIndustries\Melodiia\Crud\Event;

use SwagIndustries\Melodiia\Response\DeletionCancelResponse;
use Symfony\Component\HttpFoundation\Response;

final class DeleteEvent extends CrudEvent
{
    /** @var bool */
    private $stopDelete;

    /** @var DeletionCancelResponse|null */
    private $deleteResponse;

    public function __construct($data)
    {
        parent::__construct($data);
        $this->stopDelete = false;
    }

    /**
     * @param string|null $message this message will be returned in the response body.
     * @param string|int $code this code will be the response http status code.
     */
    public function stopDelete($message = null, $code = Response::HTTP_BAD_REQUEST): void
    {
        $this->stopDelete = true;
        $this->deleteResponse = new DeletionCancelResponse($code, $message);
    }

    public function stopDeleteWithResponse(DeletionCancelResponse $deletionCancelResponse)
    {
        $this->stopDelete = true;
        $this->deleteResponse = $deletionCancelResponse;
    }

    public function isDeletionActive(): bool
    {
        return !$this->stopDelete;
    }

    public function delete(): void
    {
        $this->stopDelete = true;
    }

    /**
     * @return DeletionCancelResponse|null
     */
    public function getDeleteResponse(): ?DeletionCancelResponse
    {
        return $this->deleteResponse;
    }
}
