<?php

namespace Deozza\PhilarmonyCoreBundle\Command;

use Deozza\PhilarmonyCoreBundle\Service\DatabaseSchema\DatabaseSchemaValidator;
use Deozza\PhilarmonyCoreBundle\Service\FormManager\FormGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaGenerateFormCommand extends Command
{
    protected static $defaultName = 'philarmony:generate:form';

    public function __construct(FormGenerator $formGenerator)
    {
        $this->formGenerator = $formGenerator;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription("Update your system based on your migrations");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->formGenerator->removeAll();
        $this->formGenerator->generate();
    }
}