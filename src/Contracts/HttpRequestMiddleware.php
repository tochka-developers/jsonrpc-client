<?php

namespace Tochka\JsonRpcClient\Contracts;

use Psr\Http\Message\RequestInterface;
use Tochka\JsonRpcSupport\Contracts\HttpRequestMiddleware as BaseMiddleware;

interface HttpRequestMiddleware extends BaseMiddleware
{
    public function handleHttpRequest(RequestInterface $request, callable $next): array;
}
