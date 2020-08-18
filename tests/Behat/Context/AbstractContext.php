<?php

namespace SwagIndustries\Melodiia\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractContext implements Context
{
    private KernelInterface $kernel;
    protected Generator $faker;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->faker = Factory::create();
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}
