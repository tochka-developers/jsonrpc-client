<?php

namespace Tochka\JsonRpcClient\QueryPreparers;

use Tochka\JsonRpcClient\ClientConfig;
use Tochka\JsonRpcClient\Contracts\QueryPreparer;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

class ArrayParametersPreparer implements QueryPreparer
{
    /**
     * @param string                             $method
     * @param array                              $params
     * @param \Tochka\JsonRpcClient\ClientConfig $config
     *
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcRequest
     */
    public function prepare(string $method, array $params, ClientConfig $config): JsonRpcRequest
    {
        $id = uniqid($config->clientName, true);

        return new JsonRpcRequest($method, array_values($params), $id);
    }
}