<?php

namespace Deozza\PhilarmonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('deozza_philarmony');

        $rootNode
            ->children()
                ->arrayNode('directory')
                    ->children()
                        ->scalarNode('entity')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/entity")
                        ->end()
                        ->scalarNode('entityJoin')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/entityJoin")
                        ->end()
                        ->scalarNode('property')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/property")
                        ->end()
                        ->scalarNode('type')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/type")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;

    }
}