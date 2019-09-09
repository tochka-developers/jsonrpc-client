<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Request;

class AuthTokenMiddleware
{
    public function handle(
        Request $request,
        \Closure $next,
        TransportClient $client,
        $value,
        $name = 'X-Access-Key'
    ) {
        if (!$client instanceof HttpClient) {
            return $next($request);
        }

        $client->setHeader($name, $value);

        return $next($request);
    }
}