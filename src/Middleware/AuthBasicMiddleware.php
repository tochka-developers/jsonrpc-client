<?php

namespace Tochka\JsonRpcClient\Middleware;

use GuzzleHttp\RequestOptions;
use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

class AuthBasicMiddleware implements OnceExecutedMiddleware
{
    /**
     * @param JsonRpcRequest[] $requests
     * @param \Closure         $next
     * @param TransportClient  $client
     * @param string           $username
     * @param string           $password
     * @param string           $scheme
     *
     * @return mixed
     */
    public function handle(
        array $requests,
        \Closure $next,
        TransportClient $client,
        $username = '',
        $password = '',
        $scheme = 'basic'
    ) {
        if (!$client instanceof HttpClient) {
            return $next($requests);
        }

        $client->setOption(RequestOptions::AUTH, [
            $username,
            $password,
            $scheme,
        ]);

        return $next($requests);
    }
}