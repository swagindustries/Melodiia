<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Response;

use SwagIndustries\Melodiia\Response\Model\SerializationContext;

interface SerializationContextAwareInterface
{
    public function getSerializationContext(): SerializationContext;

    public function setSerializationContext(SerializationContext $context);
}
