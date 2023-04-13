<?php

namespace Tochka\JsonRpcClient\Middleware;

use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddlewareInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestContainer;

/**
 * @psalm-api
 */
class AuthBasicMiddleware implements HttpRequestMiddlewareInterface
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

    public function handleHttpRequest(JsonRpcRequestContainer $request, callable $next): ?ResponseInterface
    {
        $httpRequest = $request->getRequest();

        $header = sprintf(
            '%s %s',
            $this->scheme,
            base64_encode($this->username . ':' . $this->password)
        );

        $httpRequest = $httpRequest->withHeader('Authorization', $header);

        $request->setRequest($httpRequest);

        return $next($request);
    }
}
