<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Documentation;

use OpenApi\Analysis;

interface DocumentationFactoryInterface
{
    public function createOpenApiAnalysis(): Analysis;
}
