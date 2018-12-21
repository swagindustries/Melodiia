<?php

namespace Biig\Happii\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('happii');

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
            ->end()
        ;

        return $treeBuilder;
    }
}
