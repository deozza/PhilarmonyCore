<?php


namespace Deozza\PhilarmonyBundle\Rules;


use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;

interface RuleInterface
{
    public function supports($entity, $method) : bool;

    public function decide($entity, $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader);
}