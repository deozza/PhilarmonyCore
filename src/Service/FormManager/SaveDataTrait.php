<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;

trait SaveDataTrait{
    private function saveData($data, $entityToProcess, $formKind, $formFields)
    {
        $this->default = [];
        $propertiesOfEntity = $entityToProcess->getProperties();
        if(!is_array($data))
        {
            $field = $formFields[0];
            $data = base64_encode($data);
            $fieldConfig = $this->schemaLoader->loadPropertyEnumeration($field);
            $formFields = [ $field => $fieldConfig];

            if(isset($fieldConfig['array']))
            {

                $data = [$data];
            }

            $data = [$field => $data];

        }


        if(empty($propertiesOfEntity))
        {
            $data = $this->saveNewData($data, $formFields);
        }
        else
        {
            $data = $this->mergeData($data,$propertiesOfEntity, $formFields, $formKind);
        }
        
        $entityToProcess->setProperties($data);
        $this->em->persist($entityToProcess);
    }

    private function saveNewData($data, $formFields)
    {
        $subData = [];
        foreach($formFields as $field=>$config)
        {

            if(isset($config['constraints']['automatic']))
            {
                $value = $this->addDefaultValue($config['constraints']['automatic']);
                $data = array_merge($data, [$field=>$value]);
            }

            if(isset($config['constraints']['default']) && !isset($data[$field]))
            {

                $value = $this->addDefaultValue($config['constraints']['default']);
                $data = array_merge($data, [$field=>$value]);
            }

            if(isset($config['arrayOf']))
            {
                $data[$config['arrayOf']] = [];
            }
        }

        foreach ($formFields as $field=>$config)
        {
            if(isset($config['arrayOf']))
            {
                if(!isset($subData[$config['arrayOf']]))
                {
                    $subData[$config['arrayOf']] = [];
                }
                    $subData[$config['arrayOf']][$field] = $data[$field];
                    unset($data[$field]);
            }
        }

        if(!empty($subData))
        {
            foreach ($subData as $key=>$value)
            {
                $data[$key] = [$value];
            }
        }

        return $data;
    }

    private function mergeData($data, $propertiesOfEntity, $formFields, $formKind)
    {
        $data = $this->saveNewData($data, $formFields);
        foreach($data as $key=>$value)
        {
            if(!isset($propertiesOfEntity[$key]))
            {
                $propertiesOfEntity[$key] = $value;
            }
            else if(is_array($propertiesOfEntity[$key]))
            {
                if($formKind === "post")
                {
                    $propertiesOfEntity[$key] = array_merge($propertiesOfEntity[$key], $value);
                }
                else
                {
                    $propertiesOfEntity[$key] = $value;
                }
            }
            else
            {
                $propertiesOfEntity[$key] = $value;
            }
        }
        return $propertiesOfEntity;
    }

    private function addDefaultValue($default)
    {
        $default = explode('.', $default);
        $value = $default[0];
        if($value === "date")
        {
            $value = new \DateTime($default[1]);
            $value = $value->format('Y-m-d H:i:s');
        }
        return $value;
    }
}