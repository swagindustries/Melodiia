<?php

declare(strict_types=1);

namespace SwagIndustries\Melodiia\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('melodiia');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC for symfony/config < 4.2
            $rootNode = $treeBuilder->root('melodiia');
        }

        $rootNode
            ->children()
                ->arrayNode('apis')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('base_path')->defaultValue('/')->end()
                            ->arrayNode('pagination')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('max_per_page_attribute')->defaultValue('max_per_page')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('form_extensions')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('datetime')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
