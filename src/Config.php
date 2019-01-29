<?php

namespace Tochka\JsonRpcClient;

class Config
{
    public $serviceName = 'default';
    public $clientName = 'default';
    public $defaultConnection = 'api';
    public $connections = [];

    public $url;
    public $clientClass;
    public $extendedStubs = false;
    public $middleware;

    public static function create(string $serviceName = null): self
    {
        $instance = new self();

        $instance->serviceName = $serviceName ?? config('jsonrpc-client.default');

        $instance->clientName = config('jsonrpc-client.clientName');
        $instance->url = config('jsonrpc-client.connections.' . $serviceName . '.url');
        $instance->middleware = config('jsonrpc-client.connections.' . $serviceName . '.middleware', []);
        $instance->clientClass = config('jsonrpc-client.connections.' . $serviceName . '.clientClass');
        $instance->extendedStubs = config('jsonrpc-client.connections.' . $serviceName . '.extendedStubs', false);

        return $instance;
    }
}