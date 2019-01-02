<?php

namespace Biig\Melodiia\Documentation;

use OpenApi\Analysis;

interface DocumentationFactoryInterface
{
    public function createOpenApiAnalysis(): Analysis;
}
