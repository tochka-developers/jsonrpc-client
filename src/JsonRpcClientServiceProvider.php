<?php

namespace Tochka\JsonRpcClient;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\ServiceProvider;
use Tochka\JsonRpcClient\Client\HttpClient;
use Tochka\JsonRpcClient\Console\GenerateClient;
use Tochka\JsonRpcClient\Contracts\MiddlewareRegistryInterface;

/**
 * @codeCoverageIgnore
 */
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
        $defaultTimeout = config('jsonrpc-client.defaultTimeout', null);
        
        $this->app->singleton(MiddlewareRegistryInterface::class, function() use ($services, $clientName) {
            $middlewareRegistry = new MiddlewareRegistry();
            foreach ($services as $alias => $serviceConfig) {
                $config = new ClientConfig($clientName, $alias, $serviceConfig);
                $middlewareRegistry->setMiddleware($alias, $config->middleware, $config->onceExecutedMiddleware);
            }
            
            return $middlewareRegistry;
        });

        foreach ($services as $alias => $serviceConfig) {
            $clientClass = $serviceConfig['clientClass'] ?? null;
            if (class_exists($clientClass)) {
                $this->app->singleton($clientClass, function () use ($clientName, $alias, $serviceConfig, $defaultTimeout) {
                    $config = new ClientConfig($clientName, $alias, $serviceConfig);
                    if ($defaultTimeout !== null) {
                        $config->options += [RequestOptions::TIMEOUT => $defaultTimeout];
                    }
                    
                    $client = new HttpClient($config->options);
                    $queryPreparer = $this->app->get($config->queryPreparer);

                    return new Client($config, $queryPreparer, $client);
                });
            }
        }
    }
}
