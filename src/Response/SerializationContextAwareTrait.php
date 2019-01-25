<?php

namespace Biig\Melodiia\Response;

use Biig\Melodiia\Response\Model\SerializationContext;

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
