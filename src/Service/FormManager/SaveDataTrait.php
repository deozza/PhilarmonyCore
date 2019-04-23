<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;

trait SaveDataTrait{
    private function saveData($data, $entityToProcess, $formKind)
    {
        $propertiesOfEntity = $entityToProcess->getProperties();


        foreach($data as $property=>$value)
        {
            $item = $this->schemaLoader->loadPropertyEnumeration($property);

            if($formKind ==  "patch")
            {
                if(array_key_exists('multiple', $item) && $item['multiple'] == true)
                {
                    array_push($propertiesOfEntity[$property], $value);
                }
                else
                {
                    $propertiesOfEntity[$property] = $value;
                }
            }
            else
            {
                if(!empty($propertiesOfEntity[$property]))
                {
                    if($propertiesOfEntity[$property] != $value){

                    }

                    if(is_array($value))
                    {
                        array_push($propertiesOfEntity[$property], $value);
                    }
                }
                else
                {
                    if(is_array($value))
                    {
                        $value = [$value];
                    }
                    $propertiesOfEntity[$property] = $value;
                }

            }

        }

        $kind = $entityToProcess->getKind();

        if(is_array($propertiesOfEntity))
        {
            $kind = key($propertiesOfEntity);
        }

        $defaultProperties = $this->schemaLoader->loadEntityEnumeration($kind);


        foreach($defaultProperties['properties'] as $defaultProperty)
        {
            $hasDefault = $this->schemaLoader->loadPropertyEnumeration($defaultProperty);

            if(array_key_exists('default', $hasDefault) && $hasDefault['default'] !== null)
            {
                if(!array_key_exists($defaultProperty, $propertiesOfEntity) || empty($propertiesOfEntity[$defaultProperty]))
                {

                    $default = explode('.', $hasDefault['default']);

                    if($default[0] === "date")
                    {
                        $default[0] = new \DateTime($default[1]);
                        $default[0] = $default[0]->format('Y-m-d H:i:s');
                    }


                    if(is_array($propertiesOfEntity))
                    {
                        $propertiesOfEntity[$kind][0] = array_merge($propertiesOfEntity[$kind][0], [$defaultProperty => $default[0]]) ;
                    }
                    else
                    {
                        $propertiesOfEntity[$defaultProperty] = $default[0];
                    }
                }
            }
        }

        $entityToProcess->setProperties($propertiesOfEntity);
        $this->em->persist($entityToProcess);
    }
}