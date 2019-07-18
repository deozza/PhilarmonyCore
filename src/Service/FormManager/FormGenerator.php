<?php


namespace Deozza\PhilarmonyCoreBundle\Service\FormManager;


use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyUtils\DataSchema\AuthorizedKeys;

class FormGenerator
{
    public function __construct(DatabaseSchemaLoader $schemaLoader)
    {
        $this->schemaLoader = $schemaLoader;
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

    public function generate(string $rootPath)
    {
        $entities = $this->schemaLoader->loadEntityEnumeration()[AuthorizedKeys::ENTITY_HEAD];
        $this->rootPath = $rootPath;
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

    private function generateForm(string $entity, string $state, string $method,  $properties)
    {
        $dirPath = $this->rootPath."/src/Form/$entity/$state";
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
                foreach($embeddedProperties as $embeddedProperty)
                {
                    $propertyConfig = $this->schemaLoader->loadPropertyEnumeration($embeddedProperty);
                    $config = [];
                    $type = explode('.',$propertyConfig['type']);
                    $config['type'] = $type[0];

                    $config['constraints'] = $propertyConfig['constraints'];
                    if($type[0] === "enumeration")
                    {
                        $config['constraints']['choices'] = $this->schemaLoader->loadEnumerationEnumeration($type[1]);
                    }

                    if(array_key_exists('array', $propertiesConfig))
                    {
                        $config['array'] = true;
                    }
                    $propertiesConfig[$embeddedProperty] = $config;
                }
                continue;
            }

            if(array_key_exists('array', $propertyConfig))
            {
                $config['array'] = true;
            }

            $propertiesConfig[$property] = $config;
        }
        $twig = $this->getTwigEnvironment();
        $content = $twig->render('form.php.twig', ['properties'=>$propertiesConfig, 'classname'=>$method, 'namespace'=>$entity.'\\'.$state]);
        file_put_contents($filePath, $content);

        echo $filePath." has been created \n";
    }
}