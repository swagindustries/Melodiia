<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Serialization\Context;

use SwagIndustries\Melodiia\Response\ApiResponse;

interface ContextBuilderChainInterface
{
    public function buildContext(array $context, ApiResponse $response): array;
}
