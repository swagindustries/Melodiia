<?php

namespace Biig\Melodiia\Bridge\Symfony\DependencyInjection;

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
                            ->arrayNode('paths')->prototype('scalar')->end()->defaultValue([])->end()
                            ->booleanNode('enable_doc')->defaultTrue()->end()
                            ->scalarNode('doc_factory')->defaultNull()->end()
                            ->scalarNode('base_path')->defaultValue('/')->end()
                            ->scalarNode('title')->defaultNull()->end()
                            ->scalarNode('description')->defaultNull()->end()
                            ->scalarNode('version')->defaultValue('1.0.0')->end()
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
