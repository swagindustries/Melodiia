<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

use SwagIndustries\Melodiia\Response\Model\SerializationContext;

trait SerializationContextAwareTrait
{
    /** @var SerializationContext */
    private $serializationContext;

    public function getSerializationContext(): SerializationContext
    {
        return $this->serializationContext;
    }

    public function setSerializationContext(SerializationContext $context)
    {
        $this->serializationContext = $context;
    }
}
