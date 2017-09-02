<?php

namespace Lamb\Converter;

class PostmanTest extends \PHPUnit\Framework\TestCase
{
    public function testCollection()
    {
        $collection = \Lamb\CollectionStructureFactory::fromDir();
        $environment = \Lamb\EnvironmentStructureFactory::fromDir();
        \Lamb\Converter\Postman::collection($collection, 'build');
        \Lamb\Converter\Postman::environment($environment, 'build');
        \Lamb\Converter\Swagger::document($collection, $environment, 'sample_env', 'build');
        \Lamb\Converter\ApiBlueprint::document($collection, 'build');
        $this->assertTrue(true);
    }
}
