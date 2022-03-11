<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Tochka\JsonRpcClient\Contracts\QueryPreparer;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\Exceptions\ResponseException;
use Tochka\JsonRpcClient\Facades\JsonRpcClientMiddlewareRepository;
use Tochka\JsonRpcClient\Facades\PsrHttpClient;
use Tochka\JsonRpcClient\Middleware\MiddlewarePipeline;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;
use Tochka\JsonRpcClient\Standard\JsonRpcResponse;
use Tochka\JsonRpcClient\Support\ClientConfig;
use Tochka\JsonRpcClient\Support\Request;
use Tochka\JsonRpcClient\Support\Result;

/**
 * @method static static batch()
 * @method static static cache($minutes = -1)
 * @method static static with(string $name, $value)
 * @method static static withValues(array $values)
 * @method static execute()
 * @method static mixed call(string $method, array $params)
 */
class JsonRpcClient
{
    private string $clientName;
    private ClientConfig $config;
    private bool $executeImmediately = true;
    
    /** @var array<Request> */
    private array $requests = [];
    /** @var array<Result> */
    private array $results = [];
    private array $additionalValues = [];
    private QueryPreparer $queryPreparer;
    private Container $container;
    
    public function __construct(
        string $clientName,
        ClientConfig $config,
        QueryPreparer $queryPreparer,
        Container $container
    ) {
        $this->clientName = $clientName;
        $this->config = $config;
        $this->queryPreparer = $queryPreparer;
        $this->container = $container;
        
        $this->reset();
    }
    
    public function __call($method, $params)
    {
        if (method_exists($this, '_' . $method)) {
            return $this->{'_' . $method}(...$params);
        }
        
        return $this->_call($method, $params);
    }
    
    protected function _batch(): self
    {
        $this->reset();
        $this->executeImmediately = false;
        
        return $this;
    }
    
    protected function _with(string $name, $value): self
    {
        $this->additionalValues[$name] = $value;
        
        return $this;
    }
    
    protected function _withValues(array $values): self
    {
        $this->additionalValues = array_merge($this->additionalValues, $values);
        
        return $this;
    }
    
    protected function _cache($minutes = -1): self
    {
        $this->additionalValues['cache'] = $minutes;
        
        return $this;
    }
    
    /** @noinspection MagicMethodsValidityInspection */
    protected function _call(string $method, array $params)
    {
        $jsonRpcRequest = $this->queryPreparer->prepare($method, $params, $this->config);
        
        $request = new Request($jsonRpcRequest, $this->clientName);
        $request->setAdditional($this->additionalValues);
        
        $this->additionalValues = [];
        
        $this->requests[$request->getId()] = $request;
        $this->results[$request->getId()] = $this->requests[$request->getId()]->getResult();
        
        if ($this->executeImmediately) {
            $result = $this->_execute();
            if (\count($result) > 0) {
                return $result[0];
            }
        }
        
        return $this->results[$request->getId()];
    }
    
    protected function _execute(): array
    {
        $pipeline = new Pipeline($this->container);
        
        $httpRequest = PsrHttpClient::createHttpRequest($this->config->url);
        
        return $pipeline->send($httpRequest)
            ->via('handleHttpRequest')
            ->through(JsonRpcClientMiddlewareRepository::getMiddlewareForHttpRequest($this->clientName))
            ->then(function (RequestInterface $httpRequest) {
                return $this->handleHttpRequest($httpRequest);
            });
    }
    
    /**
     * @throws JsonRpcClientException
     */
    private function handleHttpRequest(RequestInterface $httpRequest): array
    {
        $executedRequests = $this->getJsonRpcRequests();
        
        if (count($executedRequests) === 0) {
            $this->reset();
            return [];
        }
        
        try {
            try {
                $rawResponses = PsrHttpClient::send($httpRequest, $executedRequests);
            } catch (\JsonException $e) {
                throw new JsonRpcClientException(JsonRpcClientException::CODE_RESPONSE_PARSE_ERROR, null, $e);
            } catch (ClientExceptionInterface $e) {
                throw new JsonRpcClientException(JsonRpcClientException::CODE_HTTP_REQUEST_ERROR, null, $e);
            } catch (\Throwable $e) {
                throw new JsonRpcClientException(JsonRpcClientException::CODE_UNKNOWN_REQUEST_ERROR, null, $e);
            }
            
            if (!\is_array($rawResponses)) {
                $rawResponses = [$rawResponses];
            }
            
            $responses = array_map(static fn($response) => new JsonRpcResponse($response), $rawResponses);
            
            foreach ($responses as $response) {
                if (isset($this->requests[$response->id])) {
                    $this->requests[$response->id]->setJsonRpcResponse($response);
                    $this->results[$response->id] = $this->requests[$response->id]->getResult();
                } else {
                    if (!empty($response->error)) {
                        throw new ResponseException($response->error);
                    }
                    
                    throw new JsonRpcClientException(0, 'Unknown response');
                }
            }
            
            return array_values(
                array_map(static function (Result $item) {
                    return $item->get();
                }, $this->results)
            );
        } finally {
            $this->reset();
        }
    }
    
    private function getJsonRpcRequests(): array
    {
        $executedRequests = [];
        
        foreach ($this->requests as $request) {
            $requestPipeline = new Pipeline($this->container);
            
            /** @var JsonRpcRequest|null $jsonRpcRequest */
            $jsonRpcRequest = $requestPipeline->send($request)
                ->via('handleJsonRpcRequest')
                ->through(JsonRpcClientMiddlewareRepository::getMiddlewareForJsonRpcRequest($this->clientName))
                ->then(function (Request $request) {
                    return ($request->getResult() instanceof Result) ? $request->getJsonRpcRequest() : null;
                });
            
            if ($jsonRpcRequest !== null) {
                $executedRequests[] = $jsonRpcRequest->toArray();
            }
        }
        
        return $executedRequests;
    }
    
    private function reset(): void
    {
        $this->executeImmediately = true;
        $this->requests = [];
        $this->additionalValues = [];
        $this->results = [];
    }
}
