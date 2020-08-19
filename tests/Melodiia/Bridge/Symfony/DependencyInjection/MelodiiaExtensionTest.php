<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\Test\Bridge\Symfony\DependencyInjection;

use PHPUnit\Framework\TestCase;
use SwagIndustries\Melodiia\Bridge\Symfony\DependencyInjection\MelodiiaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MelodiiaExtensionTest extends TestCase
{
    public function testItCreateServicesIfDocEnabled()
    {
        $extension = new MelodiiaExtension();

        $config = [[
            'apis' => [
                'main' => [
                    'base_path' => '/api/v1',
                ],
                'foo' => [
                ],
            ],
        ]];

        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', __DIR__ . '/../../../../..');
        $extension->load($config, $container);

        $this->assertTrue($container->hasDefinition('melodiia.documentation'));
    }
}
