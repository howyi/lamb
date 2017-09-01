<?php

namespace Lamb;

class PostmanGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testCollection()
    {
        $collection = \Lamb\CollectionStructureFactory::fromDir();
        $environment = \Lamb\EnvironmentStructureFactory::fromDir();
        \Lamb\PostmanGenerator::collection($collection, 'build');
        \Lamb\PostmanGenerator::environment($environment, 'build');
        \Lamb\SwaggerGenerator::document($collection, $environment, 'sample_env', 'build');
        $this->assertTrue(true);
    }
}
