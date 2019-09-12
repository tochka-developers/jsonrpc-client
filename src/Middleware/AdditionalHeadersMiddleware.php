<?php

namespace Tochka\JsonRpcClient\Middleware;

use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Request;

/**
 * A middleware allowing for inclusion of additional http headers into the request.
 *
 * @package App\Api\Middleware
 */
class AdditionalHeadersMiddleware
{
    public function handle(Request $request, \Closure $next, TransportClient $client, $headers = [])
    {
        if (!$client instanceof HttpClient) {
            return $next($request);
        }

        foreach ($headers as $key => $value) {
            if (!\is_array($value)) {
                $value = [$value];
            }

            foreach ($value as $element) {
                $client->setHeader($key, $element);
            }
        }

        return $next($request);
    }
}
