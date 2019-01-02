<?php

namespace Biig\Melodiia;

interface MelodiiaConfigurationInterface
{
    /**
     * Return [api_name => [base_path, path]]
     * only for conf with doc enabled.
     *
     * @return array
     */
    public function getDocumentationConfig(): array;
}
