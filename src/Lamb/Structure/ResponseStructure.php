<?php

namespace Lamb\Structure;

class ResponseStructure extends AbstractBodyStructure
{
    private $description;
    private $header;

    /**
     * @param string $description
     * @param array  $header
     * @param array  $body
     */
    public function __construct(
        string $description,
        array $header,
        array $body
    ) {
        $this->description = $description;
        $this->header = $header;
        $this->body = $body;
    }
}
