<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Container\Container;
use Tochka\JsonRpcClient\Contracts\QueryPreparer;
use Tochka\JsonRpcClient\Contracts\TransportClient;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\Exceptions\ResponseException;
use Tochka\JsonRpcClient\Facades\MiddlewareRegistry as MiddlewareRegistryFacade;
use Tochka\JsonRpcClient\Middleware\MiddlewarePipeline;
use Tochka\JsonRpcClient\Standard\JsonRpcRequest;

/**
 * Class Client
 *
 * @method static static batch()
 * @method static static cache($minutes = -1)
 * @method static static with(string $name, $value)
 * @method static static withValues(array $values)
 * @method static execute()
 * @method static mixed call(string $method, array $params)
 */
class Client
{
    protected ClientConfig $config;

    protected bool $executeImmediately = true;

    /** @var Request[] */
    protected array $requests = [];

    protected array $results = [];

    protected array $additionalValues = [];

    protected QueryPreparer $queryPreparer;

    protected TransportClient $transportClient;

    public function __construct(ClientConfig $config, QueryPreparer $queryPreparer, TransportClient $client)
    {
        $this->reset();

        $this->config = $config;
        $this->queryPreparer = $queryPreparer;
        $this->transportClient = $client;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function __call($method, $params)
    {
        if (method_exists($this, '_'.$method)) {
            return $this->{'_'.$method}(...$params);
        }

        return $this->_call($method, $params);
    }

    /**
     * Помечает экземпляр клиента как массив вызовов
     *
     * @return $this
     */
    protected function _batch(): self
    {
        $instanceBatch = new self($this->config, $this->queryPreparer, $this->transportClient);
        $instanceBatch->executeImmediately = false;

        return $instanceBatch;
    }

    /**
     * @param  mixed  $value
     */
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

    /**
     * Помечает вызываемый метод кешируемым
     *
     * @param  int  $minutes
     * @return $this
     */
    protected function _cache($minutes = -1): self
    {
        $this->additionalValues['cache'] = $minutes;

        return $this;
    }

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * Выполняет удаленный вызов (либо добавляет его в массив)
     *
     * @param  string  $method
     * @param  array  $params
     * @return mixed
     *
     * @throws \Exception
     */
    protected function _call($method, $params)
    {
        $jsonRpcRequest = $this->queryPreparer->prepare($method, $params, $this->config);
        $request = new Request($jsonRpcRequest);
        $request->setAdditional($this->additionalValues);

        $this->additionalValues = [];

        $pipeline = new MiddlewarePipeline(Container::getInstance());
        $pipeline->setAdditionalDIInstances($this->config, $this->transportClient);

        $request = $pipeline->send($request)
            ->through(MiddlewareRegistryFacade::getMiddleware($this->config->serviceName))
            ->via('handle')
            ->thenReturn();

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

    /**
     * Выполняет запрос всех вызовов
     *
     * @throws \Exception
     */
    protected function _execute(): array
    {
        $pipeline = new MiddlewarePipeline(Container::getInstance());
        $pipeline->setAdditionalDIInstances($this->config, $this->transportClient);

        $jsonRpcRequests = array_values(
            array_filter(
                array_map(function (Request $request): ?JsonRpcRequest {
                    return ($request->getResult() instanceof Result)
                        ? $request->getJsonRpcRequest()
                        : null;
                }, $this->requests)
            )
        );

        try {
            return $pipeline->send($jsonRpcRequests)
                ->through(MiddlewareRegistryFacade::getOnceExecutedMiddleware($this->config->serviceName))
                ->via('handle')
                ->then(function (array $requests) {
                    return $this->sendRequests($requests);
                });
        } finally {
            $this->reset();
        }
    }

    private function sendRequests(array $requests): array
    {
        if (! \count($requests)) {
            $this->reset();

            return [];
        }

        $responses = $this->transportClient->get($requests, $this->config);

        foreach ($responses as $response) {
            if (isset($this->requests[$response->id])) {
                $this->requests[$response->id]->setJsonRpcResponse($response);
                $this->results[$response->id] = $this->requests[$response->id]->getResult();
            } else {
                if (! empty($response->error)) {
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
    }

    protected function reset(): void
    {
        $this->executeImmediately = true;
        $this->requests = [];
        $this->additionalValues = [];
        $this->results = [];
    }
}
