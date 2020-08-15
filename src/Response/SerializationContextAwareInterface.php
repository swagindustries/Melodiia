<?php

namespace SwagIndustries\Melodiia\Response;

use SwagIndustries\Melodiia\Response\Model\SerializationContext;

interface SerializationContextAwareInterface
{
    public function getSerializationContext(): SerializationContext;

    public function setSerializationContext(SerializationContext $context);
}
