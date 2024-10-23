<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\ClientConfig;

interface TransportClient
{
    /**
     * @param  \Tochka\JsonRpcClient\Standard\JsonRpcRequest[]  $request
     * @return \Tochka\JsonRpcClient\Standard\JsonRpcResponse[]
     */
    public function get(array $request, ClientConfig $config): array;
}
