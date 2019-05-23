<?php

namespace Deozza\PhilarmonyCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('philarmony');

        $rootNode
            ->children()
                ->arrayNode('directory')
                    ->children()
                        ->scalarNode('entity')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/entity")
                        ->end()
                        ->scalarNode('property')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/property")
                        ->end()
                        ->scalarNode('enumeration')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/enumeration")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;

    }
}