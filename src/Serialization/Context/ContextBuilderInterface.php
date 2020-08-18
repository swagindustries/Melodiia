<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Serialization\Context;

use SwagIndustries\Melodiia\Response\ApiResponse;

/**
 * Build a part of the serialization context.
 */
interface ContextBuilderInterface extends ContextBuilderChainInterface
{
    /**
     * Return true if the buildContext method can be called.
     */
    public function supports(ApiResponse $response): bool;
}
