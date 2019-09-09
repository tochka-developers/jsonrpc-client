<?php

namespace Tochka\JsonRpcClient;

use Tochka\JsonRpcClient\QueryPreparers\DefaultQueryPreparer;

class ClientConfig
{
    public $serviceName = 'default';
    public $clientName = 'default';

    public $url;
    public $clientClass;
    public $extendedStubs = false;
    public $middleware;
    public $queryPreparer;

    public function __construct(string $clientName, string $serviceName, array $clientConfig)
    {
        $this->clientName = $clientName;
        $this->serviceName = $serviceName;

        if (!isset($clientConfig['url'], $clientConfig['clientClass'])) {
            throw new \RuntimeException('Connection configuration mismatch for: ' . $serviceName);
        }

        $this->url = $clientConfig['url'];
        $this->clientClass = $clientConfig['clientClass'];

        $this->middleware = $this->parseMiddleware($clientConfig['middleware'] ?? []);

        $this->extendedStubs = $clientConfig['extendedStubs'] ?? false;
        $this->queryPreparer = $clientConfig['queryPreparer'] ?? DefaultQueryPreparer::class;
    }

    protected function parseMiddleware($middleware): array
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
}