<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpc\Standard\Contracts\MiddlewareInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;

/**
 * @psalm-api
 */
interface JsonRpcRequestMiddlewareInterface extends MiddlewareInterface
{
    /**
     * @param JsonRpcClientRequest $request
     * @param callable(JsonRpcClientRequest): ?JsonRpcClientRequest $next
     * @return JsonRpcClientRequest
     */
    public function handleJsonRpcRequest(JsonRpcClientRequest $request, callable $next): JsonRpcClientRequest;
}
