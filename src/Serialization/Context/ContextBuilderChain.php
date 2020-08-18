<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Serialization\Context;

use SwagIndustries\Melodiia\Response\ApiResponse;

final class ContextBuilderChain implements ContextBuilderChainInterface
{
    /** @var ContextBuilderInterface[] */
    private $builders;

    public function __construct(iterable $builders)
    {
        $this->builders = $builders;
    }

    public function buildContext(array $context, ApiResponse $response): array
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($response)) {
                $context = $builder->buildContext($context, $response);
            }
        }

        return $context;
    }
}
