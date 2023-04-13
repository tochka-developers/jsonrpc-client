<?php

namespace Tochka\JsonRpcClient\Contracts;

use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcResponseCollection;

interface ResponseParserInterface
{
    public function parse(string $connectionName, ResponseInterface $response): JsonRpcResponseCollection;
}
