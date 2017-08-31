<?php

namespace Lamb\Structure;

class CollectionStructure
{
    private $name;
    private $apiList;

    /**
     * @param string $name
     * @param array  $apiList
     */
    public function __construct(
        string $name,
        array $apiList
    ) {
        $this->name = $name;
        $this->apiList = $apiList;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getApiList(): array
    {
        return $this->apiList;
    }
}
