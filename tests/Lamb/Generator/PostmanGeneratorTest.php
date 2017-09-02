<?php

namespace Lamb\Generator;

class PostmanTest extends \PHPUnit\Framework\TestCase
{
    public function testCollection()
    {
        $collection = \Lamb\CollectionStructureFactory::fromDir();
        $environment = \Lamb\EnvironmentStructureFactory::fromDir();
        \Lamb\Generator\Postman::collection($collection, 'build');
        \Lamb\Generator\Postman::environment($environment, 'build');
        \Lamb\Generator\Swagger::document($collection, $environment, 'sample_env', 'build');
        \Lamb\Generator\ApiBlueprint::document($collection, 'build');
        $this->assertTrue(true);
    }
}
