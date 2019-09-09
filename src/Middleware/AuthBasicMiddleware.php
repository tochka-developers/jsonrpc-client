<?php

namespace Tochka\JsonRpcClient\Middleware;

use GuzzleHttp\RequestOptions;
use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Request;

class AuthBasicMiddleware
{
    public function handle(
        Request $request,
        \Closure $next,
        TransportClient $client,
        $username = '',
        $password = '',
        $scheme = 'basic'
    ) {
        if (!$client instanceof HttpClient) {
            return $next($request);
        }

        $client->setOption(RequestOptions::AUTH, [
            $username,
            $password,
            $scheme,
        ]);

        return $next($request);
    }
}