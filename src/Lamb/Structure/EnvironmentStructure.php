<?php

namespace Lamb\Structure;

class EnvironmentStructure
{
    private $name;
    private $environmentList;

    /**
     * @param string $name
     * @param array  $environmentList
     */
    public function __construct(
        string $name,
        array $environmentList
    ) {
        $this->name = $name;
        $this->environmentList = $environmentList;
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
    public function getEnvironmentList(): array
    {
        return $this->environmentList;
    }
}
