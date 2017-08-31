<?php

namespace Lamb\Command;

use Lamb\Util\Config;
use Lamb\CollectionStructureFactory;
use Lamb\EnvironmentStructureFactory;
use Lamb\PostmanGenerator;
use Lamb\SwaggerGenerator;
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

        $a = PostmanGenerator::collection($collection, 'build');
        $a = PostmanGenerator::environment($environment, 'build');

        $a = SwaggerGenerator::document($collection, $environment, 'sample_env', 'build');
    }
}
