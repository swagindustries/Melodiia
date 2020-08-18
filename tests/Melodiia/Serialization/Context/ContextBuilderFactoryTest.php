<?php

declare(strict_types=1);

namespace Melodiia\Serialization\Context;

use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\Response\ApiResponse;
use SwagIndustries\Melodiia\Serialization\Context\ContextBuilderChain;
use SwagIndustries\Melodiia\Serialization\Context\ContextBuilderInterface;

class ContextBuilderFactoryTest extends TestCase
{
    public function testItBuildContextUsingGivenBuilders()
    {
        $builder1 = new class() implements ContextBuilderInterface {
            public function buildContext(array $context, ApiResponse $response): array
            {
                $context['foo'] = true;

                return $context;
            }

            public function supports(ApiResponse $response): bool
            {
                return true;
            }
        };
        $builder2 = new class() implements ContextBuilderInterface {
            public function buildContext(array $context, ApiResponse $response): array
            {
                $context['baz'] = true;

                return $context;
            }

            public function supports(ApiResponse $response): bool
            {
                return false;
            }
        };
        $builder3 = new class() implements ContextBuilderInterface {
            public function buildContext(array $context, ApiResponse $response): array
            {
                $context['bar'] = true;

                return $context;
            }

            public function supports(ApiResponse $response): bool
            {
                return true;
            }
        };

        $chain = new ContextBuilderChain([$builder1, $builder2, $builder3]);

        $context = $chain->buildContext([], $this->prophesize(ApiResponse::class)->reveal());
        $this->assertTrue($context['foo']);
        $this->assertTrue($context['bar']);
        $this->assertFalse(isset($context['baz']));
    }
}
