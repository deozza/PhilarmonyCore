<?php

namespace Deozza\PhilarmonyCoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder('philarmony');
        $treeBuilder->getRootNode()
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
                        ->scalarNode('formPath')
                            ->isRequired()
                            ->treatNullLike("/src/Form/")
                        ->end()
                        ->scalarNode('formNamespace')
                            ->isRequired()
                            ->treatNullLike("App\\Form\\")
                        ->end()
                    ->end()
                ->end()
                ->enumNode('orm')
                    ->values(['mysql', 'mongodb'])
                    ->isRequired()
                    ->treatNullLike("mysql")
                ->end()
            ->end()
        ;

        return $treeBuilder;

    }
}