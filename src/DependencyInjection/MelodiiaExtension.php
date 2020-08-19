<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\DependencyInjection;

use Doctrine\Persistence\AbstractManagerRegistry;
use SwagIndustries\Melodiia\Crud\FilterInterface;
use SwagIndustries\Melodiia\Serialization\Context\ContextBuilderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Twig\Environment;

class MelodiiaExtension extends Extension
{
    const TAG_CRUD_FILTER = 'melodiia.crud_filter';
    const TAG_CONTEXT_BUILDER = 'melodiia.context_builder';

    public function load(array $configs, ContainerBuilder $container)
    {
        $configFileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $configFileLocator);
        $loader->load('services.yaml');
        $xmlLoader = new XmlFileLoader($container, $configFileLocator);
        $xmlLoader->load('error-management.xml');

        if (class_exists(AbstractManagerRegistry::class)) {
            $loader->load('doctrine.yaml');
        }
        if ($container->hasAlias('melodiia.data_provider')) {
            $loader->load('crud.yaml');
        }
        if (class_exists(Environment::class)) {
            $loader->load('twig.yaml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('melodiia.config', $config);
        $this->disableFormExtensionIfNeeded($container, $config['form_extensions']);

        // Autoconf
        $container->registerForAutoconfiguration(FilterInterface::class)->addTag(self::TAG_CRUD_FILTER);
        $container
            ->registerForAutoconfiguration(ContextBuilderInterface::class)
            ->addTag(self::TAG_CONTEXT_BUILDER);
    }

    private function disableFormExtensionIfNeeded(ContainerBuilder $builder, array $config)
    {
        if (!$config['datetime']) {
            $builder->removeDefinition('melodiia.form.extension.datetime');
        }
    }

    private function getServiceName(string $apiName, string $serviceName)
    {
        return 'melodiia.' . $apiName . '.' . $serviceName;
    }

    public function getAlias()
    {
        return 'melodiia';
    }
}
