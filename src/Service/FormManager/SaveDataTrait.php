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
            if($formKind ==  "patch")
            {
                $propertiesOfEntity[$property]= $value;
            }
            else
            {
                $item = $this->schemaLoader->loadPropertyEnumeration($property);

                if(array_key_exists('multiple', $item) && $item['multiple'] == true)
                {
                   if(empty($propertiesOfEntity[$property]))
                   {
                       $propertiesOfEntity[$property] = [$value];
                   }
                   else
                   {
                       array_push($propertiesOfEntity[$property], $value);
                   }
                }
                else
                {
                    $propertiesOfEntity[$property] = $value;
                }

            }
        }

        $entityToProcess->setProperties($propertiesOfEntity);

        $this->em->persist($entityToProcess);
    }
}