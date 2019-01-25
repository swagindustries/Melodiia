<?php

namespace Biig\Melodiia\Response;

use Biig\Melodiia\Response\Model\SerializationContext;

interface SerializationContextAwareInterface
{
    public function getSerializationContext(): SerializationContext;

    public function setSerializationContext(SerializationContext $context);
}
