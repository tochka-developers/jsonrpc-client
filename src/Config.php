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

        $instance->serviceName = $serviceName ?? config('jsonrpcclient.default');

        $instance->clientName = config('jsonrpcclient.clientName');
        $instance->url = config('jsonrpcclient.connections.' . $serviceName . '.url');
        $instance->middleware = config('jsonrpcclient.connections.' . $serviceName . '.middleware', []);
        $instance->clientClass = config('jsonrpcclient.connections.' . $serviceName . '.clientClass');
        $instance->extendedStubs = config('jsonrpcclient.connections.' . $serviceName . '.extendedStubs', false);

        return $instance;
    }
}