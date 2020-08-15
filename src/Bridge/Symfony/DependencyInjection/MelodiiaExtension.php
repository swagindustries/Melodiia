<?php

namespace SwagIndustries\Melodiia\Bridge\Symfony\DependencyInjection;

use SwagIndustries\Melodiia\Bridge\Symfony\Exception\ConfigException;
use SwagIndustries\Melodiia\Bridge\Symfony\Routing\CrudDocumentationFactory;
use SwagIndustries\Melodiia\Crud\FilterInterface;
use SwagIndustries\Melodiia\Documentation\Controller\OpenApiController;
use SwagIndustries\Melodiia\Documentation\Controller\OpenApiJsonController;
use SwagIndustries\Melodiia\Documentation\OpenApiDocFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class MelodiiaExtension extends Extension
{
    const TAG_CRUD_FILTER = 'melodiia.crud_filter';

    public function load(array $configs, ContainerBuilder $container)
    {
        $configFileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $configFileLocator);
        $loader->load('services.yaml');
        $xmlLoader = new XmlFileLoader($container, $configFileLocator);
        $xmlLoader->load('error-management.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['apis'] as $name => $apiConf) {
            $this->configureApi($name, $apiConf, $container);
        }

        $container->setParameter('melodiia.config', $config);
        $this->disableFormExtensionIfNeeded($container, $config['form_extensions']);

        // Autoconf
        $container->registerForAutoconfiguration(FilterInterface::class)->addTag(self::TAG_CRUD_FILTER);
    }

    private function configureApi(string $name, array $apiConf, ContainerBuilder $container)
    {
        if ($apiConf['enable_doc']) {
            if (empty($apiConf['paths'])) {
                $defaultPath = $container->getParameter('kernel.project_dir') . '/src';
                if (!file_exists($defaultPath)) {
                    throw new ConfigException('Impossible to find your sources directory. You need to specify path for your API.');
                }
                $apiConf['paths'][] = $defaultPath;
            }

            // Register documentation factory
            $openApiFactory = new Definition(OpenApiDocFactory::class);
            $openApiFactory->setAutowired(true);
            $apiConf['title'] = $apiConf['title'] ?? $name;
            $openApiFactory->setArgument(1, $apiConf);

            $crudDocumentation = new Definition(CrudDocumentationFactory::class);

            $factoryServiceName = $this->getServiceName($name, 'open_api_doc_factory');
            $crudDocServiceName = $this->getServiceName($name, 'crud_documentation');

            $container->setDefinition($factoryServiceName, $openApiFactory);
            $crudDocumentation->setDecoratedService($factoryServiceName);
            $crudDocumentation->setArgument(0, new Reference($crudDocServiceName . '.inner'));
            $crudDocumentation->setArgument(1, new Reference('router'));
            $crudDocumentation->setArgument(2, $apiConf['base_path']);
            $container->setDefinition($crudDocServiceName, $crudDocumentation);

            // Register doc controllers
            $jsonControllerDefinition = new Definition(OpenApiJsonController::class);
            $jsonControllerDefinition->setAutowired(true);
            $jsonControllerDefinition->setArgument(0, $apiConf['paths']);
            $jsonControllerDefinition->setArgument(1, new Reference($factoryServiceName));
            $jsonControllerDefinition->addTag('controller.service_arguments');

            $viewControllerDefinition = new Definition(OpenApiController::class);
            $viewControllerDefinition->setAutowired(true);
            $viewControllerDefinition->addTag('controller.service_arguments');

            $container->setDefinition($this->getServiceName($name, 'open_api_view_controller'), $viewControllerDefinition);
            $container->setDefinition($this->getServiceName($name, 'open_api_json_controller'), $jsonControllerDefinition);
        }
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
