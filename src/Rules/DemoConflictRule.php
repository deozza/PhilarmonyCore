<?php
namespace App\Rules;

use Deozza\PhilarmonyBundle\Service\DatabaseSchemaLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DemoConflictRule
{
    const ERROR_IS_REQUIRED = "PROPERTY_IS_REQUIRED";

    public function supports($context, Request $request)
    {
        return true;
    }

    public function decide($object,Request $request, EntityManagerInterface $em, DatabaseSchemaLoader $schemaLoader)
    {
        return;
    }
}