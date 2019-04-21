<?php

namespace Biig\Melodiia;

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
}
