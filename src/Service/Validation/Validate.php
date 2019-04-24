<?php

namespace Deozza\PhilarmonyBundle\Service\Validation;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;

class Validate
{

    public function __construct(ResponseMaker $responseMaker, DatabaseSchemaLoader $schemaLoader, EntityManagerInterface $em)
    {
        $this->response = $responseMaker;
        $this->schemaLoader = $schemaLoader;
        $this->em = $em;
    }

    public function processValidation(Entity $entity)
    {
        $hasValidation = $this->schemaLoader->loadValidationEnumeration();
        $state = null;

        if(!array_key_exists($entity->getKind(), $hasValidation['validations']))
        {
            return $state;
        }

        foreach($hasValidation['validations'][$entity->getKind()] as $key=>$value)
        {
            if($value['constraints'] === null)
            {
                $state = $key;
                continue;
            }

            $isValid = $this->validateEntity($entity, $value['constraints']);
        }

        die;
        return ["state" =>$state, "context" =>$isValid];
    }

    private function validateEntity(Entity $entity, array $constraints)
    {
        $this->errors = [];
        foreach($constraints as $property=>$constraint)
        {

            $support = explode('.', $property);
            if($support[0] === "properties")
            {
                if(!isset($entity->getProperties()[$support[1]]))
                {
                    $this->errors = [$support[1] => "This value cannot be null"];
                    continue;
                }
                $valueSubmited = $entity->getProperties()[$support[1]];

                $this->choseFunction($valueSubmited, $constraint);

            }

        }
    }

    private function choseFunction($submited, $functionName)
    {
        



        if(sizeof($functionName) == 1)
        {
            $function = explode('(', $function)[0];
        }

        $this->$function($submited, $function);
    }

    private function greaterThanOrEqual($submited, $constraint)
    {
        if($submited instanceof \DateTime)
        {
            $dateSubmited = $submited->format('y-m-d');
        }
        var_dump($constraint);die;

    }

    private function lesserThanOrEqual($submited, $constraint)
    {

    }

    private function greaterThan($submited, $constraint)
    {

    }

    private function lesserThan($submited, $constraint)
    {

    }

    private function equal($submited, $constraint)
    {

    }
}