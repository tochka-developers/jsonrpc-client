<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpc\Standard\DTO\JsonRpcResponse;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestCollection;
use Tochka\JsonRpcClient\DTO\JsonRpcResponseCollection;

interface ResultMapperInterface
{
    public function mapCollection(JsonRpcRequestCollection $requests, JsonRpcResponseCollection $responses): void;

    public function map(JsonRpcClientRequest $request, JsonRpcResponse $response): void;
}
