<?php

namespace Lamb\Command;

use Lamb\Util\Config;
use Lamb\CollectionStructureFactory;
use Lamb\EnvironmentStructureFactory;
use Lamb\Generator\Postman;
use Lamb\Generator\Swagger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends Command
{
    protected function configure()
    {
        $this->setName('check');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = CollectionStructureFactory::fromDir();
        $environment = EnvironmentStructureFactory::fromDir();

        Postman::collection($collection, 'build');
        Postman::environment($environment, 'build');

        Swagger::document($collection, $environment, 'sample_env', 'build');
    }
}
