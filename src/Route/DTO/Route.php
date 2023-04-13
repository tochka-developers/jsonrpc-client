<?php

namespace Tochka\JsonRpcClient\Route\DTO;

class Route
{
    public string $connectionName;
    public string $clientClassName;
    public string $clientClassMethod;
    public string $method;
    /** @var array<string, Parameter> */
    public array $parameters = [];
    public ?Parameter $result;

    public function __construct(string $connectionName, string $clientClassName, string $clientClassMethod, string $method)
    {
        $this->connectionName = $connectionName;
        $this->clientClassName = $clientClassName;
        $this->clientClassMethod = $clientClassMethod;
        $this->method = $method;
    }
}
