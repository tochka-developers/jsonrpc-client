<?php

namespace Tochka\JsonRpcClient\Contracts;

use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Support\Request;
use Tochka\JsonRpcSupport\Contracts\JsonRpcRequestMiddleware as BaseMiddleware;

interface JsonRpcRequestMiddleware extends BaseMiddleware
{
    public function handleJsonRpcRequest(Request $request, callable $next): JsonRpcRequest;
}
