<?php

namespace Biig\Melodiia;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * @internal inheritance
 */
interface MelodiiaConfigurationInterface
{
    /**
     * Return [api_name => [base_path, path]]
     * only for conf with doc enabled.
     *
     * @return array
     */
    public function getDocumentationConfig(): array;

    /**
     * Return all base path of Melodiia.
     *
     * @return array
     */
    public function getApiEndpoints(): array;

    /**
     * Return the api configuration for the given request.
     * Or null if the request don't match Melodiia's apis.
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function getApiConfigFor(Request $request): ?array;
}
