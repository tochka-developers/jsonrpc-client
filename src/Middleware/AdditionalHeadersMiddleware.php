<?php

namespace Tochka\JsonRpcClient\Middleware;

use Psr\Http\Message\ResponseInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddlewareInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestContainer;

/**
 * A middleware allowing for inclusion of additional http headers into the request.
 * @psalm-api
 */
class AdditionalHeadersMiddleware implements HttpRequestMiddlewareInterface
{
    private array $headers;

    public function __construct(array $headers = [])
    {
        $this->headers = $headers;
    }

    public function handleHttpRequest(JsonRpcRequestContainer $request, callable $next): ?ResponseInterface
    {
        $httpRequest = $request->getRequest();

        foreach ($this->headers as $key => $headers) {
            if (!is_array($headers)) {
                $headers = [$headers];
            }

            foreach ($headers as $headerValue) {
                $httpRequest = $httpRequest->withAddedHeader($key, $headerValue);
            }
        }

        $request->setRequest($httpRequest);

        return $next($request);
    }
}
