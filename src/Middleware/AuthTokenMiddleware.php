<?php

namespace Tochka\JsonRpcClient\Middleware;

use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddlewareInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestContainer;

/**
 * @psalm-api
 */
class AuthTokenMiddleware implements HttpRequestMiddlewareInterface
{
    private string $token;
    private string $headerName;

    public function __construct(string $token, string $headerName = 'X-Access-Key')
    {
        $this->token = $token;
        $this->headerName = $headerName;
    }

    public function handleHttpRequest(JsonRpcRequestContainer $request, callable $next): ?ResponseInterface
    {
        $httpRequest = $request->getRequest();

        $httpRequest = $httpRequest->withHeader($this->headerName, $this->token);

        $request->setRequest($httpRequest);

        return $next($request);
    }
}
