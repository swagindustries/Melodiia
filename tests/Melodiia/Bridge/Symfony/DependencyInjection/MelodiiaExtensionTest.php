<?php

namespace SwagIndustries\Melodiia\Test\Bridge\Symfony\DependencyInjection;

use SwagIndustries\Melodiia\Bridge\Symfony\DependencyInjection\MelodiiaExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MelodiiaExtensionTest extends TestCase
{
    public function testItCreateServicesIfDocEnabled()
    {
        $extension = new MelodiiaExtension();

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
        $container->setParameter('kernel.project_dir', __DIR__ . '/../../../../..');
        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('melodiia.main.open_api_view_controller'));
        $this->assertTrue($container->hasDefinition('melodiia.main.open_api_json_controller'));
        $this->assertTrue($container->hasDefinition('melodiia.main.open_api_doc_factory'));
        $this->assertTrue($container->hasDefinition('melodiia.foo.open_api_view_controller'));
        $this->assertTrue($container->hasDefinition('melodiia.foo.open_api_json_controller'));
        $this->assertTrue($container->hasDefinition('melodiia.foo.open_api_doc_factory'));
    }

    public function testItDoesNotCreateServicesForDocIfNotEnabled()
    {
        $extension = new MelodiiaExtension();

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

        $this->assertFalse($container->hasDefinition('melodiia.main.open_api_view_controller'));
        $this->assertFalse($container->hasDefinition('melodiia.main.open_api_json_controller'));
        $this->assertFalse($container->hasDefinition('melodiia.main.open_api_doc_factory'));
    }
}
