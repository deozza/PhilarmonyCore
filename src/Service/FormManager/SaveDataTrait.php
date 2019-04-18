<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;

trait SaveDataTrait{
    private function saveData($data, $entityToProcess)
    {
        if (is_a($entityToProcess, Entity::class)) {
            return $this->saveEntity($data, $entityToProcess);
        } else {
            return $this->saveProperty($data, $entityToProcess);
        }

    }

    private function saveEntity($data, $entityToProcess)
    {
        $this->em->persist($entityToProcess);

        foreach($data as $key=>$value)
        {
            if(!empty($value))
            {
                $property = new Property();
                $property->setKind($key);
                $property->setEntity($entityToProcess);

                if(is_a($value, Entity::class))
                {
                    $value = $value->getUuidAsString();
                }

                if(is_a($value, \DateTime::class))
                {
                    $value = $value->format('yyyy-MM-dd');
                }

                if(is_array($value))
                {
                    $value = json_encode($value);
                }
                $property->setValue($value);
                $this->em->persist($property);
            }
        }

        return $data;
    }

    private function saveProperty($data, $entityToProcess)
    {
        $propertySchema = $this->schemaLoader->loadPropertyEnumeration($entityToProcess->getKind());

        if(array_key_exists('constraints', $propertySchema))
        {
            if(array_key_exists('mime', $propertySchema['constraints']))
            {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($data);

                if(!in_array($mimeType, $propertySchema['constraints']['mime']))
                {
                    return $this->response->badRequest("The file must be of one the following types ".json_encode($propertySchema['constraints']['mime']));
                }

                $data = ['value' => base64_encode($data)];
            }
        }

        $entityToProcess->setValue($data['value']);
        $this->em->persist($entityToProcess);

        return true;
    }
}