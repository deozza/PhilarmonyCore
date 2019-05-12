<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;

trait SaveDataTrait{
    private function saveData($data, $entityToProcess, $formKind, $formFields)
    {
        $this->default = [];
        $propertiesOfEntity = $entityToProcess->getProperties();
        if(empty($propertiesOfEntity))
        {
            $data = $this->saveNewData($data, $formFields);
        }
        else
        {
            $data = $this->mergeData($data,$propertiesOfEntity, $formFields);
        }

/*

        if(!is_array($data))
        {
            $file = base64_encode($data);

            try
            {
                $isPropertyMultiple = $this->schemaLoader->loadPropertyEnumeration($formFields[0]);
            }
            catch(\Exception $e)
            {
                return $this->response->badRequest($e->getMessage());
            }

            $isMultiple = explode('.', $isPropertyMultiple['type']);

            if($isMultiple[0] === "array")
            {
                $file = [$file];
            }

            $data = [$formFields[0]=> $file];
        }

        $data = array_merge_recursive($data, $this->default);

        foreach($data as $property=>$value)
        {
            if(!empty($value))
            {
                if ($formKind == "patch")
                {
                    $propertiesOfEntity[$property] = $value;
                }
                else
                {
                    if (!empty($propertiesOfEntity[$property]))
                    {
                        if(!is_array($propertiesOfEntity[$property]))
                        {
                            continue;
                        }

                        if(in_array($value, $propertiesOfEntity[$property]))
                        {
                            continue;
                        }

                        if (is_array($value))
                        {
                            array_push($propertiesOfEntity[$property], $value);
                        }
                    }
                    else
                    {
                        if (is_array($value))
                        {
                            $value = [$value];
                        }
                        $propertiesOfEntity[$property] = $value;
                    }
                }
            }
        }
        */

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

    private function mergeData($data, $propertiesOfEntity, $formFields)
    {
        $data = $this->saveNewData($data, $formFields);
        foreach($data as $key=>$value)
        {
            if(!isset($propertiesOfEntity[$key]))
            {
                $propertiesOfEntity[$key] = $value;
            }
            if(is_array($propertiesOfEntity[$key]))
            {
                $propertiesOfEntity[$key] = array_merge($propertiesOfEntity[$key], $value);
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