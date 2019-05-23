<?php

namespace Deozza\PhilarmonyCoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DeozzaPhilarmonyCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__."/../Resources/config"));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);


        $definition = $container->getDefinition('philarmony.schema_loader');
        $definition->setArgument(0, $config['directory']['entity']);
        $definition->setArgument(1, $config['directory']['property']);
        $definition->setArgument(2, $config['directory']['enumeration']);
    }

    public function getAlias()
    {
        return "philarmony";
    }

}