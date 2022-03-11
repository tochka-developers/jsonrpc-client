<?php

namespace Tochka\JsonRpcClient\Facades;

use Illuminate\Support\Facades\Facade;
use Psr\Http\Message\RequestInterface;

/**
 * @method static RequestInterface createHttpRequest(string $url)
 * @method static array|object send(RequestInterface $httpRequest, array $jsonRpcRequestCollection)
 *
 * @see \Tochka\JsonRpcClient\Support\PsrHttpClient
 */
class PsrHttpClient extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return self::class;
    }
}
