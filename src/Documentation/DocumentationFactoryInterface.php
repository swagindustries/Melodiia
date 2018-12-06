<?php

namespace Biig\Happii\Documentation;

use OpenApi\Analysis;

interface DocumentationFactoryInterface
{
    public function createOpenApiAnalysis(): Analysis;
}
