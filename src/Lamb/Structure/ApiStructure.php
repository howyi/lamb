<?php

namespace Lamb\Structure;

class ApiStructure
{
    private $description;
    private $request;
    private $response;

    /**
     * @param string            $description
     * @param RequestStructure  $request
     * @param ResponseStructure $response
     */
    public function __construct(
        string $description,
        RequestStructure $request,
        ResponseStructure $response
    ) {
        $this->description = $description;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return RequestStructure
     */
    public function getRequest(): RequestStructure
    {
        return $this->request;
    }

    /**
     * @return ResponseStructure
     */
    public function getResponse(): ResponseStructure
    {
        return $this->response;
    }
}
