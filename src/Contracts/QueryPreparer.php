<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Support\ClientConfig;

interface QueryPreparer
{
    public function prepare(string $methodName, array $params, ClientConfig $config): JsonRpcRequest;
}
