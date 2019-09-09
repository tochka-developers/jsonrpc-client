<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Support\ServiceProvider;
use Tochka\JsonRpcClient\Client\HttpClient;
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
        $clientName = config('jsonrpc-client.clientName', []);

        foreach ($services as $alias => $serviceConfig) {
            $config = new ClientConfig($clientName, $alias, $serviceConfig);

            if (class_exists($config->clientClass)) {
                $this->app->singleton($config->clientClass, function () use ($config) {
                    $client = new HttpClient();
                    $queryPreparer = $this->app->get($config->queryPreparer);

                    return new Client($config, $queryPreparer, $client);
                });
            }
        }
    }
}