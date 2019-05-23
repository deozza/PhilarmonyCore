<?php

namespace Deozza\PhilarmonyCoreBundle\Service\Validation;

use Deozza\PhilarmonyCoreBundle\Entity\Entity;

trait CompareTrait
{

    private function getCompareTo($valueToCompare, $compareTo)
    {
        $properties = explode(".", $valueToCompare);
        for($i = 0; $i < count($properties); $i++)
        {
            if(is_object($compareTo))
            {
                $get = "get".ucfirst($properties[$i]);
                $compareTo = $compareTo->getProperties()[$properties[$i]];
            }
            else
            {
                $compareTo = $compareTo[$properties[$i]];
            }
        }

        return $compareTo;
    }

    private function compareEntities($operator, $valueToCompare, $entityToCompare, $submited)
    {
        if(strpos($operator, "between"))
        {
            $valuesToCompare = explode(',', $valueToCompare);
            $result = $this->em->getRepository(Entity::class)->findAllBetweenForValidate($entityToCompare, $valuesToCompare[0], $valuesToCompare[1], $submited);
        }
        else
        {
            $result = $this->em->getRepository(Entity::class)->findAllForValidate($entityToCompare, $valueToCompare, $submited, $operator);
        }

        if(substr($operator, 0, 1) === "!" && count($result) > 0)
        {
            return "Must be ".self::OPERATOR_TABLE[$operator]." others $valueToCompare";
        }
        elseif (substr($operator, 0, 1) !== "!" && count($result) === 0)
        {
            return "Must be ".self::OPERATOR_TABLE[$operator]." others $valueToCompare";
        }

        return null;
    }

    private function compareSelfBetween($operator, $valueToCompare, $entityToCompare, $submited)
    {
        $valuesToCompare = explode(",", $valueToCompare);
        $minValue = $this->getCompareTo($valuesToCompare[0], $entityToCompare->getProperties());
        $maxValue = $this->getCompareTo($valuesToCompare[1], $entityToCompare->getProperties());
        $result = $submited >= $minValue && $submited <= $maxValue;

        if(substr($operator, 0, 1) === "!" && $result === true)
        {
            return "Must be ".self::OPERATOR_TABLE[$operator]." self $valueToCompare";
        }
        elseif (substr($operator, 0, 1) !== "!" && $result === false)
        {
            return "Must be ".self::OPERATOR_TABLE[$operator]." self $valueToCompare";
        }
        return null;
    }

    private function compareDate($submited, $operator, $compareTo)
    {
        $submitedTimeStamp = $submited->getTimestamp();
        $compareToTimeStamp  = $compareTo->getTimestamp();
        $result = $this->compareWithEval($submitedTimeStamp , $operator, $compareToTimeStamp );
        if(!empty($result))
        {
            return "Must be ".self::OPERATOR_TABLE[$operator]." to ".$compareTo->format('Y-m-d');
        }

        return null;
    }

    private function compareWithEval($submited, $operator, $compareTo)
    {
        eval("\$result =  '$submited' $operator '$compareTo';");
        if($result === true)
        {
            return "Must be ".self::OPERATOR_TABLE[$operator]." to $compareTo";
        }
        return null;
    }
}