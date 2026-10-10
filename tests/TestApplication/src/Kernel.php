<?php

declare(strict_types=1);

namespace TestApplication;

use Doctrine\ORM\Configuration as OrmConfiguration;
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

        // Doctrine ORM 3.4+ relies on PHP native lazy objects on PHP 8.4+, older setups use generated proxies
        if (PHP_VERSION_ID >= 80400 && method_exists(OrmConfiguration::class, 'enableNativeLazyObjects')) {
            $container->loadFromExtension('doctrine', ['orm' => ['enable_native_lazy_objects' => true]]);
        } else {
            $container->loadFromExtension('doctrine', ['orm' => ['auto_generate_proxy_classes' => true]]);
        }
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
