<?php

namespace Biig\Happii\Test\Bridge\Symfony\DependencyInjection;

use Biig\Happii\Bridge\Symfony\DependencyInjection\HappiiExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HappiiExtensionTest extends TestCase
{
    public function testItCreateServicesIfDocEnabled()
    {
        $extension = new HappiiExtension();

        $config = [[
            'apis' => [
                'main' => [
                    'title' => 'hello',
                    'version' => '1.0.0',
                ],
                'foo' => [
                ],
            ],
        ]];

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', __DIR__ . '/../../..');
        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('happii.main.open_api_view_controller'));
        $this->assertTrue($container->hasDefinition('happii.main.open_api_json_controller'));
        $this->assertTrue($container->hasDefinition('happii.main.open_api_doc_factory'));
        $this->assertTrue($container->hasDefinition('happii.foo.open_api_view_controller'));
        $this->assertTrue($container->hasDefinition('happii.foo.open_api_json_controller'));
        $this->assertTrue($container->hasDefinition('happii.foo.open_api_doc_factory'));
    }

    public function testItDoesNotCreateServicesForDocIfNotEnabled()
    {
        $extension = new HappiiExtension();

        $config = [[
            'apis' => [
                'main' => [
                    'enable_doc' => false,
                    'doc_factory' => null,
                    'base_path' => '/',
                    'title' => 'hello',
                    'version' => '1.0.0',
                ],
            ],
        ]];

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', __DIR__ . '/../../..');
        $extension->load($config, $container);

        $this->assertFalse($container->hasDefinition('happii.main.open_api_view_controller'));
        $this->assertFalse($container->hasDefinition('happii.main.open_api_json_controller'));
        $this->assertFalse($container->hasDefinition('happii.main.open_api_doc_factory'));
    }
}
