<?php

namespace Tochka\JsonRpcClient\Contracts;

use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpc\Standard\Contracts\MiddlewareInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestContainer;

/**
 * @psalm-api
 */
interface HttpRequestMiddlewareInterface extends MiddlewareInterface
{
    /**
     * @param JsonRpcRequestContainer $request
     * @param callable(JsonRpcRequestContainer): ?ResponseInterface $next
     * @return ResponseInterface|null
     */
    public function handleHttpRequest(JsonRpcRequestContainer $request, callable $next): ?ResponseInterface;
}
