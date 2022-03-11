<?php

namespace Tochka\JsonRpcClient\Support;

use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer;

class ClientConfig
{
    public string $serviceName = 'default';

    public string $url;
    public string $clientClass;
    public array $middleware = [];
    public array $onceExecutedMiddleware = [];
    public string $queryPreparer;
    
    /**
     * @throws JsonRpcClientException
     */
    public function __construct(string $serviceName, array $clientConfig)
    {
        $this->serviceName = $serviceName;

        if (!isset($clientConfig['url'], $clientConfig['clientClass'])) {
            throw new JsonRpcClientException(0, 'Connection configuration mismatch for: ' . $serviceName);
        }

        $this->url = $clientConfig['url'];
        $this->clientClass = $clientConfig['clientClass'];

        $middleware = $this->parseMiddlewareConfiguration($clientConfig['middleware'] ?? []);
        $this->sortMiddleware($middleware);
        
        $this->queryPreparer = $clientConfig['queryPreparer'] ?? DefaultQueryPreparer::class;
    }

    /**
     * @param $middleware
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function parseMiddlewareConfiguration($middleware): array
    {
        $result = [];
        foreach ($middleware as $name => $m) {
            if (\is_array($m)) {
                $result[] = [$name, $m];
            } else {
                $result[] = [$m, []];
            }
        }

        return $result;
    }

    /**
     * @param array $middleware
     */
    protected function sortMiddleware(array $middleware): void
    {
        foreach ($middleware as $m) {
            $implements = class_implements($m[0]);
            if ($implements && \in_array(OnceExecutedMiddleware::class, $implements, true)) {
                $this->onceExecutedMiddleware[] = $m;
            } else {
                $this->middleware[] = $m;
            }
        }
    }
}
