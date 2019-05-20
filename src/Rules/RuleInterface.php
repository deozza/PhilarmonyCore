<?php


namespace Deozza\PhilarmonyBundle\Rules;


use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;

interface RuleInterface
{
    public function supports($entity, $posted, $method) : bool;

    public function decide($entity, $posted, $method, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader);
}