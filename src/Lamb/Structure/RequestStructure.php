<?php

namespace Lamb\Structure;

class RequestStructure extends AbstractBodyStructure
{
    private $description;
    private $header;
    private $parameter;

    /**
     * @param string $description
     * @param array  $header
     * @param array  $parameter
     * @param array  $body
     */
    public function __construct(
        string $description,
        array $header,
        array $parameter,
        array $body
    ) {
        $this->description = $description;
        $this->header = $header;
        $this->parameter = $parameter;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * @return array
     */
    public function getParameter(): array
    {
        return $this->parameter;
    }
}
