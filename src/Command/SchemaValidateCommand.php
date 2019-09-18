<?php

namespace Deozza\PhilarmonyCoreBundle\Command;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaValidator;
use Deozza\PhilarmonyCoreBundle\Service\FormManager\FormGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaValidateCommand extends Command
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
        try
        {
            $this->schemaValidator->validateEntities();
            //$this->schemaValidator->validateProperties();
        }
        catch(\Exception $e)
        {
            $output->writeln("<error>There are errors in your data scheme config files :</error>");
            $output->writeln("<error>".$e->getMessage()."</error>");
            return;
        }

        $output->writeln("<fg=green>Data schema is okay.</>");
    }
}