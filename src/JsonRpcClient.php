<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Tochka\JsonRpc\Standard\DTO\JsonRpcRequest;
use Tochka\JsonRpcClient\Contracts\ClientMiddlewareRegistryInterface;
use Tochka\JsonRpcClient\Contracts\HttpRequestMiddlewareInterface;
use Tochka\JsonRpcClient\Contracts\JsonRpcRequestMiddlewareInterface;
use Tochka\JsonRpcClient\Contracts\ParametersPreparerInterface;
use Tochka\JsonRpcClient\Contracts\ResponseParserInterface;
use Tochka\JsonRpcClient\Contracts\ResultMapperInterface;
use Tochka\JsonRpcClient\Contracts\TransportClientInterface;
use Tochka\JsonRpcClient\DTO\JsonRpcClientRequest;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestCollection;
use Tochka\JsonRpcClient\DTO\JsonRpcRequestContainer;
use Tochka\JsonRpcClient\Support\ClientConfig;
use Tochka\JsonRpcClient\Support\ClientWithBatch;
use Tochka\JsonRpcClient\Support\ClientWithCache;

class JsonRpcClient
{
    use ClientWithCache;
    use ClientWithBatch;

    private bool $executeImmediately = true;
    private array $additionalData = [];

    private Pipeline $pipeline;
    private ClientConfig $clientConfig;
    private ClientMiddlewareRegistryInterface $middlewareRegistry;
    private TransportClientInterface $transportClient;
    private ResponseParserInterface $responseParser;
    private ResultMapperInterface $resultMapper;
    private ParametersPreparerInterface $parametersPreparer;

    public function __construct(
        Container $container,
        ClientMiddlewareRegistryInterface $middlewareRegistry,
        ParametersPreparerInterface $parametersPreparer,
        TransportClientInterface $transportClient,
        ResponseParserInterface $responseParser,
        ResultMapperInterface $resultMapper,
        ClientConfig $clientConfig,
    ) {
        $this->clientConfig = $clientConfig;
        $this->pipeline = new Pipeline($container);
        $this->middlewareRegistry = $middlewareRegistry;
        $this->transportClient = $transportClient;
        $this->responseParser = $responseParser;
        $this->resultMapper = $resultMapper;
        $this->parametersPreparer = $parametersPreparer;
    }

    /**
     * @throws BindingResolutionException
     */
    public static function getInstance(): static
    {
        return Container::getInstance()->make(static::class);
    }

    /**
     * @throws BindingResolutionException
     */
    public static function with(string $name, mixed $value): static
    {
        return static::getInstance()->_with($name, $value);
    }

    /**
     * @throws BindingResolutionException
     */
    public static function call(string $method, array $parameters = [], bool $notify = false): mixed
    {
        return static::getInstance()->_call($method, $parameters, $notify);
    }

    public function _with(string $name, mixed $value): static
    {
        $this->additionalData[$name] = $value;

        return $this;
    }

    public function _call(string $method, array $parameters = [], bool $notify = false): mixed
    {
        $middleware = $this->middlewareRegistry->getMiddleware(
            $this->clientConfig->connectionName,
            JsonRpcRequestMiddlewareInterface::class
        );

        $jsonRpcRequest = new JsonRpcRequest(
            method: $this->_getMethodName($method),
            params: $this->parametersPreparer->prepare($this, $method, $parameters),
            id:     !$notify ? $this->_getRequestId() : null
        );

        $request = new JsonRpcClientRequest($jsonRpcRequest);

        $request->setAdditionalData($this->additionalData);

        $request = $this->pipeline->send($request)
            ->through($middleware)
            ->via('handleJsonRpcRequest')
            ->thenReturn();

        if ($this->executeImmediately) {
            $requestCollection = new JsonRpcRequestCollection([$request]);
            return $this->_execute($requestCollection)[0];
        } else {
            return $request;
        }
    }

    protected function _execute(JsonRpcRequestCollection $requests): array
    {
        $middleware = $this->middlewareRegistry->getMiddleware(
            $this->clientConfig->connectionName,
            HttpRequestMiddlewareInterface::class
        );

        $request = new JsonRpcRequestContainer(
            $this->transportClient->makeRequest($this->clientConfig->url),
            $requests
        );

        $this->pipeline->send($request)
            ->through($middleware)
            ->via('handleHttpRequest')
            ->then(function (JsonRpcRequestContainer $request) {
                $response = $this->transportClient->send($request->getRequest(), $request->getJsonRpcRequests());

                if ($response !== null) {
                    $jsonRpcResponses = $this->responseParser->parse($this->clientConfig->connectionName, $response);
                    $this->resultMapper->mapCollection($request->getJsonRpcRequests(), $jsonRpcResponses);
                }

                return $response;
            });

        $this->_reset();

        return $request->getJsonRpcRequests()->getResults();
    }

    public function _reset(): void
    {
        $this->executeImmediately = true;
        $this->additionalData = [];
    }

    public function _executeImmediately(bool $value): void
    {
        $this->executeImmediately = $value;
    }

    /**
     * Override this method in client for custom method name logic
     */
    public function _getMethodName(string $method): string
    {
        [, $method] = explode('::', $method);

        return $method;
    }

    /**
     * Override this method in client for custom request id logic
     */
    public function _getRequestId(): string
    {
        return sprintf(
            '%s-%s',
            $this->clientConfig->clientName,
            Str::random(32)
        );
    }

    public function _getClientConfig(): ClientConfig
    {
        return $this->clientConfig;
    }
}
