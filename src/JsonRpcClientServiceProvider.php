<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Support\ServiceProvider;
use Tochka\JsonRpcClient\Console\GenerateClient;

class JsonRpcClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateClient::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/jsonrpc-client.php' => base_path('config/jsonrpc-client.php'),
        ], 'config');
    }

    public function register()
    {
        $services = config('jsonrpc-client.connections', []);
        foreach ($services as $alias => $serviceConfig) {
            if (class_exists($serviceConfig['clientClass'])) {
                $this->app->singleton($serviceConfig['clientClass'], function () use ($alias, $serviceConfig) {
                    return new \Tochka\JsonRpcClient\Client($alias, $serviceConfig['namedParameters'] ?? true);
                });
            }
        }
    }
}