<?php

namespace Biig\Happii\Bridge\Symfony\DependencyInjection;

use Biig\Happii\Bridge\Symfony\Exception\ConfigException;
use Biig\Happii\Documentation\Controller\OpenApiController;
use Biig\Happii\Documentation\Controller\OpenApiJsonController;
use Biig\Happii\Documentation\OpenApiDocFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class HappiiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['apis'] as $name => $apiConf) {
            $this->configureApi($name, $apiConf, $container);
        }

        $container->setParameter('happii.config', $config);
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

            // Register doc controllers
            if (null === $apiConf['doc_factory']) {
                $openApiFactory = new Definition(OpenApiDocFactory::class);
                $openApiFactory->setAutowired(true);
                $openApiFactory->setArgument(1, [
                    'title' => $apiConf['title'] ?? $name,
                    'version' => $apiConf['version'],
                    'basePath' => $apiConf['base_path'],
                ]);
            }

            $factoryServiceName = $this->getServiceName($name, 'open_api_doc_factory');

            $jsonControllerDefinition = new Definition(OpenApiJsonController::class);
            $jsonControllerDefinition->setAutowired(true);
            $jsonControllerDefinition->setArgument(0, $apiConf['paths']);
            $jsonControllerDefinition->setArgument(1, new Reference($factoryServiceName));
            $jsonControllerDefinition->addTag('controller.service_arguments');

            $viewControllerDefinition = new Definition(OpenApiController::class);
            $viewControllerDefinition->setAutowired(true);
            $viewControllerDefinition->addTag('controller.service_arguments');

            $container->setDefinition($factoryServiceName, $openApiFactory);
            $container->setDefinition($this->getServiceName($name, 'open_api_view_controller'), $viewControllerDefinition);
            $container->setDefinition($this->getServiceName($name, 'open_api_json_controller'), $jsonControllerDefinition);
        }
    }

    private function getServiceName(string $apiName, string $serviceName)
    {
        return 'happii.' . $apiName . '.' . $serviceName;
    }

    public function getAlias()
    {
        return 'happii';
    }
}
