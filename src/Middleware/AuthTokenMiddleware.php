<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

class AuthTokenMiddleware implements OnceExecutedMiddleware
{
    /**
     * @param JsonRpcRequest[] $requests
     * @param \Closure         $next
     * @param TransportClient  $client
     * @param string           $value
     * @param string           $name
     *
     * @return mixed
     */
    public function handle(
        array $requests,
        \Closure $next,
        TransportClient $client,
        $value,
        $name = 'X-Access-Key'
    ) {
        if (!$client instanceof HttpClient) {
            return $next($requests);
        }

        $client->setHeader($name, $value);

        return $next($requests);
    }
}