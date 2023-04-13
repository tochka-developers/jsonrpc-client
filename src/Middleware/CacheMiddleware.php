<?php

namespace Tochka\JsonRpcClient\Middleware;

use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Tochka\JsonRpc\Standard\DTO\JsonRpcRequest;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddlewareInterface;
use Tochka\JsonRpcClient\Contracts\JsonRpcRequestMiddlewareInterface;
use Tochka\JsonRpcClient\Contracts\ResultMapperInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestContainer;

class CacheMiddleware implements JsonRpcRequestMiddlewareInterface, HttpRequestMiddlewareInterface
{
    public const DATA_KEY_CACHE = '_cache_result';

    private ?string $store;
    private ResultMapperInterface $resultMapper;

    public function __construct(ResultMapperInterface $resultMapper, string $store = null)
    {
        $this->resultMapper = $resultMapper;
        $this->store = $store;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function handleJsonRpcRequest(JsonRpcClientRequest $request, callable $next): JsonRpcClientRequest
    {
        if ($request->getAdditional(self::DATA_KEY_CACHE) === null) {
            return $next($request);
        }

        $cacheKey = $this->getCacheKey($request->getRequest());

        if (Cache::store($this->store)->has($cacheKey)) {
            $jsonRpcResponse = Cache::store($this->store)->get($cacheKey);

            $this->resultMapper->map($request, $jsonRpcResponse);

            $request->setAdditional(self::DATA_KEY_CACHE, null);
        }

        return $next($request);
    }

    public function handleHttpRequest(JsonRpcRequestContainer $request, callable $next): ?ResponseInterface
    {
        $response = $next($request);

        foreach ($request->getJsonRpcRequests()->get() as $jsonRpcRequest) {
            if (
                $jsonRpcRequest->getAdditional(self::DATA_KEY_CACHE) === null
                || $jsonRpcRequest->getResponse() === null
            ) {
                continue;
            }

            Cache::store($this->store)->put(
                $this->getCacheKey($jsonRpcRequest->getRequest()),
                $jsonRpcRequest->getResponse()
            );
        }

        return $response;
    }

    private function getCacheKey(JsonRpcRequest $request): string
    {
        return 'str';
    }
}
