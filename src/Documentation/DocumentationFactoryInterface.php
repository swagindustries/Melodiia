<?php

namespace SwagIndustries\Melodiia\Documentation;

use OpenApi\Analysis;

interface DocumentationFactoryInterface
{
    public function createOpenApiAnalysis(): Analysis;
}
