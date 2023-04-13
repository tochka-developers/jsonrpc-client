<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Contracts\JsonRpcRequestMiddlewareInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;

class LogMiddleware implements JsonRpcRequestMiddlewareInterface
{
    public function handleJsonRpcRequest(JsonRpcClientRequest $request, callable $next): JsonRpcClientRequest
    {
        return $next($request);
    }
}
