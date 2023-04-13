<?php

namespace Tochka\JsonRpcClient\Support;

/**
 * @psalm-import-type ClientConfigArray from ClientConfig
 */
class ClientsConfig
{
    /** @var array<string, ClientConfig> */
    public array $clientsConfig = [];

    /**
     * @param array<string, ClientConfigArray> $config
     */
    public function __construct(array $config, ?string $clientName)
    {
        foreach ($config as $connectionName => $clientConfig) {
            $this->clientsConfig[$connectionName] = new ClientConfig($clientConfig, $connectionName, $clientName);
        }
    }
}
