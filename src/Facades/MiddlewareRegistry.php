<?php

namespace Tochka\JsonRpcClient\Facades;

use Illuminate\Support\Facades\Facade;
use Tochka\JsonRpcClient\Contracts\MiddlewareRegistryInterface;

/**
 * @method static void setMiddleware(string $clientName, array $middleware, array $onceExecutedMiddleware)
 * @method static void append($middleware, ?string $clientName = null)
 * @method static void prepend($middleware, ?string $clientName = null)
 * @method static array getMiddleware(string $clientName)
 * @method static array getOnceExecutedMiddleware(?string $clientName)
 */
class MiddlewareRegistry extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return MiddlewareRegistryInterface::class;
    }
}
