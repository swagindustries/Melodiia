<?php

declare(strict_types=1);

namespace TestApplication;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        //        $container->import('../config/config.yaml');
        //        $container->import('../config/melodiia.yaml');
        $loader->load(__DIR__ . '/../config/config.yaml');
        $loader->load(__DIR__ . '/../config/melodiia.yaml');
    }

    protected function configureRoutes($routes): void
    {
        $routes->import('../config/routing.yaml');
        $routes->import('../config/routing_dev.yaml');
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    /**
     * BC Layer for Symfony 4.4.
     */
    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }
}
