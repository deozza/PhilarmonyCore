<?php
namespace Deozza\PhilarmonyCoreBundle\Service\FormManager;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyUtils\DataSchema\AuthorizedKeys;

class FormGenerator
{
    public function __construct(DatabaseSchemaLoader $schemaLoader, string $formPath, string $formNamespace, string $rootPath)
    {
        $this->schemaLoader = $schemaLoader;
        $this->formPath = $formPath;
        $this->formNamespace = $formNamespace;
        $this->rootPath = $rootPath;
    }

    public function getFormPath()
    {
        return $this->rootPath."/".$this->formPath;
    }

    public function getFormNamespace()
    {
        return $this->formNamespace;
    }

    protected function render($template, $parameters)
    {
        $twig = $this->getTwigEnvironment();

        return $twig->render($template, $parameters);
    }

    protected function getTwigEnvironment()
    {
        $skeletonDirs = [
            dirname(__FILE__).'/../../Resources/skeleton'
        ];
        return new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDirs), array(
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        ));
    }

    public function generate()
    {
        $entities = $this->schemaLoader->loadEntityEnumeration()[AuthorizedKeys::ENTITY_HEAD];
        foreach($entities as $entityName => $entityContent)
        {
            if(!array_key_exists(AuthorizedKeys::ENTITY_KEYS[1], $entityContent))
            {
                continue;
            }

            foreach($entityContent[AuthorizedKeys::ENTITY_KEYS[1]] as $state => $stateContent)
            {
                foreach($stateContent[AuthorizedKeys::STATE_KEYS[0]] as $method => $methodContent)
                {
                    if($method === AuthorizedKeys::METHODS[0] || $method === AuthorizedKeys::METHODS[1])
                    {
                        $this->generateForm($entityName, $state, $method, $methodContent[AuthorizedKeys::METHOD_KEYS[0]]);
                    }
                }
            }
        }
    }

    private function generateForm(string $entity, string $state, string $method,  $properties, $subDir = null)
    {
        $dirPath = $this->getFormPath()."$entity/$state";

        if(!empty($subDir))
        {
            $dirPath .= '/'.$subDir;
        }
        $filePath = $dirPath."/".$method.'.php';

        if(!is_dir($dirPath))
        {
            mkdir($dirPath, $mode = 0777, $recursive = true);
        }

        if($properties === "all")
        {
            $properties = $this->schemaLoader->loadEntityEnumeration($entity)['properties'];
        }

        $propertiesConfig = [];
        foreach($properties as $property)
        {
            $propertyConfig = $this->schemaLoader->loadPropertyEnumeration($property);
            $config = [];
            $type = explode('.',$propertyConfig['type']);
            $config['type'] = $type[0];

            $config['constraints'] = $propertyConfig['constraints'];
            if($type[0] === "enumeration")
            {
                $config['constraints']['choices'] = $this->schemaLoader->loadEnumerationEnumeration($type[1]);
            }

            if($type[0] === "embedded")
            {
                $embeddedProperties = $this->schemaLoader->loadEntityEnumeration($type[1])['properties'];
                $this->generateForm($entity, $state, $method, $embeddedProperties, $type[1]);
                continue;
            }

            if($type[0] === 'entity')
            {
                $config['constraint']['entity'] = $type[1];
            }

            if(array_key_exists('array', $propertyConfig))
            {
                $config['array'] = true;
            }

            $propertiesConfig[$property] = $config;
        }

        $namespace = $this->formNamespace.$entity.'\\'.$state;
        if(!empty($subDir))
        {
            $namespace .= '\\'.$subDir;
        }

        $twig = $this->getTwigEnvironment();
        $content = $twig->render('form.php.twig', ['properties'=>$propertiesConfig, 'classname'=>$method, 'namespace'=>$namespace]);
        file_put_contents($filePath, $content);

        echo $filePath." has been created \n";
    }
}