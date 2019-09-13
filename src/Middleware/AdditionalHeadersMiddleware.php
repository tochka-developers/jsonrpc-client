<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

/**
 * A middleware allowing for inclusion of additional http headers into the request.
 *
 * @package App\Api\Middleware
 */
class AdditionalHeadersMiddleware implements OnceExecutedMiddleware
{
    /**
     * @param JsonRpcRequest[] $requests
     * @param \Closure         $next
     * @param TransportClient  $client
     * @param array            $headers
     *
     * @return mixed
     */
    public function handle(array $requests, \Closure $next, TransportClient $client, $headers = [])
    {
        if (!$client instanceof HttpClient) {
            return $next($requests);
        }

        foreach ($headers as $key => $value) {
            if (!\is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $element) {
                $client->setHeader($key, $element);
            }
        }

        return $next($requests);
    }
}
