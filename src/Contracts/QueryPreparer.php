<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

interface QueryPreparer
{
    public function prepare(string $method, array $params, ClientConfig $config): JsonRpcRequest;
}