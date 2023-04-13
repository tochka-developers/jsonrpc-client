<?php

namespace Tochka\JsonRpcClient\Support;

/**
 * @psalm-api
 * @psalm-type ClientConfigArray = array{
 *   url: string,
 *   clientClass: string,
 *   openrpc?: string,
 *   parametersByName?: boolean,
 *   middleware?: array<string, array>|array<string>
 * }
 */
class ClientConfig
{
    public string $clientName;
    public string $connectionName;
    public string $url;
    public ?string $openRpc;
    /** @var class-string */
    public string $clientClass;
    public bool $parametersByName;
    /** @var array<string, array>|array<string>|array */
    public array $middleware = [];

    /**
     * @param ClientConfigArray $config
     * @param string $connectionName
     * @param string|null $clientName
     */
    public function __construct(array $config, string $connectionName, ?string $clientName)
    {
        $this->connectionName = $connectionName;
        $this->clientName = $clientName ?? 'default';
        $this->url = $config['url'];
        $this->clientClass = $config['clientClass'];
        $this->openRpc = $config['openRpc'] ?? null;
        $this->parametersByName = $config['parametersByName'] ?? true;
        $this->middleware = $config['middleware'] ?? [];
    }
}
