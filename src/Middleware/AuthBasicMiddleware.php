<?php

namespace Tochka\JsonRpcClient\Middleware;

use Psr\Http\Message\RequestInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddleware;

class AuthBasicMiddleware implements HttpRequestMiddleware
{
    public const SCHEME_BASIC = 'Basic';
    public const SCHEME_DIGEST = 'Digest';
    
    private string $username;
    private string $password;
    private string $scheme;
    
    public function __construct(string $username = '', string $password = '', string $scheme = self::SCHEME_BASIC)
    {
        $this->username = $username;
        $this->password = $password;
        $this->scheme = $scheme;
    }
    
    public function handleHttpRequest(RequestInterface $request, callable $next): array
    {
        $header = sprintf(
            '%s %s',
            $this->scheme,
            base64_encode($this->username . ':' . $this->password)
        );
        $request = $request->withHeader('Authorization', $header);
        
        return $next($request);
    }
}
