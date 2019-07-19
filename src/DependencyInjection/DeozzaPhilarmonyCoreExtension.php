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


        $definitionSchema = $container->getDefinition('philarmony.schema_loader');
        $definitionSchema->setArgument(0, $config['directory']['entity']);
        $definitionSchema->setArgument(1, $config['directory']['property']);
        $definitionSchema->setArgument(2, $config['directory']['enumeration']);

        $definitionForm = $container->getDefinition('philarmony.form_generator');
        $definitionForm->setArgument(1, $config['directory']['formPath']);
        $definitionForm->setArgument(2, $config['directory']['formNamespace']);

    }

    public function getAlias()
    {
        return "philarmony";
    }

}