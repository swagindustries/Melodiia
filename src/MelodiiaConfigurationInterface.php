<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia;

use Symfony\Component\HttpFoundation\Request;

/**
 * @internal for testing purpose, you should implement this interface
 */
interface MelodiiaConfigurationInterface
{
    /**
     * Return all base path of Melodiia.
     */
    public function getApiEndpoints(): array;

    /**
     * Return the api configuration for the given request.
     * Or null if the request don't match Melodiia's apis.
     */
    public function getApiConfigFor(Request $request): ?array;
}
