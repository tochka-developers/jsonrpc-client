<?php

namespace Tochka\JsonRpcClient\Middleware;

use Psr\Http\Message\RequestInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddleware;

/**
 * A middleware allowing for inclusion of additional http headers into the request.
 */
class AdditionalHeadersMiddleware implements HttpRequestMiddleware
{
    private array $headers;
    
    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }
    
    public function handleHttpRequest(RequestInterface $request, callable $next): array
    {
        foreach ($this->headers as $key => $value) {
            $request = $request->withHeader($key, $value);
        }
        
        return $next($request);
    }
}
