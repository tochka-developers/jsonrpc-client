<?php

namespace Tochka\JsonRpcClient;

use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;
use Tochka\JsonRpcClient\Exceptions\JsonRpcClientException;
use Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer;

class ClientConfig
{
    public $serviceName = 'default';
    public $clientName = 'default';

    public $url;
    public $clientClass;
    public $extendedStubs = false;
    public $middleware = [];
    public $onceExecutedMiddleware = [];
    public $queryPreparer;
    public $options = [];

    /**
     * ClientConfig constructor.
     *
     * @param string $clientName
     * @param string $serviceName
     * @param array  $clientConfig
     *
     * @throws \Tochka\JsonRpcClient\Exceptions\JsonRpcClientException
     */
    public function __construct(string $clientName, string $serviceName, array $clientConfig)
    {
        $this->clientName = $clientName;
        $this->serviceName = $serviceName;

        if (!isset($clientConfig['url'], $clientConfig['clientClass'])) {
            throw new JsonRpcClientException(0, 'Connection configuration mismatch for: ' . $serviceName);
        }

        $this->url = $clientConfig['url'];
        $this->clientClass = $clientConfig['clientClass'];

        $middleware = $this->parseMiddlewareConfiguration($clientConfig['middleware'] ?? []);
        $this->sortMiddleware($middleware);

        $this->extendedStubs = $clientConfig['extendedStubs'] ?? false;
        $this->queryPreparer = $clientConfig['queryPreparer'] ?? DefaultQueryPreparer::class;
        $this->options = $clientConfig['options'] ?? [];
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
