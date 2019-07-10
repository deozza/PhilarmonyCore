<?php

namespace Deozza\PhilarmonyCoreBundle\Command;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaMigrationDiffCommand extends Command
{
    protected static $defaultName = 'philarmony:migration:diff';

    public function __construct(DatabaseSchemaValidator $schemaValidator)
    {
        $this->schemaValidator = $schemaValidator;
        parent::__construct();
    }

    protected function configure()
    {
       $this->setDescription("Generate a migration for your data schema");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isValid = true;
        $isValid = $this->schemaValidator->validateEntity();
    }
}