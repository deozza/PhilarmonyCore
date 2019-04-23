<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;

trait SaveDataTrait{
    private function saveData($data, $entityToProcess, $formKind)
    {
        $this->default = [];

        $propertiesOfEntity = $entityToProcess->getProperties();
        $this->addDefaultValue($entityToProcess->getKind());

        $data = array_merge_recursive($data, $this->default);

        foreach($data as $property=>$value)
        {

            if($formKind ==  "patch")
            {
                $propertiesOfEntity[$property] = $value;
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


        $entityToProcess->setProperties($propertiesOfEntity);
        $this->em->persist($entityToProcess);
    }

    private function addDefaultValue($kind, $embedded = false)
    {
        $defaultProperties = $this->schemaLoader->loadEntityEnumeration($kind);
        foreach($defaultProperties['properties'] as $value)
        {
            $property = explode('.', $this->schemaLoader->loadPropertyEnumeration($value)['type']);

            $isEmbedded = array_search("embedded", $property);
            if($isEmbedded)
            {
                $keyToRemove = array_search($value, $defaultProperties['properties']);
                unset($defaultProperties['properties'][$keyToRemove]);

                $this->default[$property[$isEmbedded + 1]] = [];
                $this->addDefaultValue($property[$isEmbedded + 1], true);
            }

            $property = $this->schemaLoader->loadPropertyEnumeration($value);
            if(isset($property['default']) && $property['default'] !== null)
            {
                $defaultValue = explode('.', $property['default']);

                if($defaultValue[0] === "date")
                {
                    $defaultValue[0] = new \DateTime($defaultValue[1]);
                    $defaultValue[0] = $defaultValue[0]->format('Y-m-d H:i:s');
                }
                if($embedded)
                {
                    $this->default[$kind][$value] = $defaultValue[0];
                }
                else
                {
                    $this->default[$value] = $defaultValue[0];
                }
            }
        }
    }
}