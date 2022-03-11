<?php

namespace Tochka\JsonRpcClient\Middleware;

use Psr\Http\Message\RequestInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddleware;

class AuthTokenMiddleware implements HttpRequestMiddleware
{
    public const DEFAULT_HEADER = 'X-Access-Key';
    
    private string $token;
    private string $header;
    
    public function __construct(string $value, string $name = self::DEFAULT_HEADER)
    {
        $this->token = $value;
        $this->header = $name;
    }
    
    public function handleHttpRequest(RequestInterface $request, callable $next): array
    {
        $request = $request->withHeader($this->header, $this->token);
        
        return $next($request);
    }
}
