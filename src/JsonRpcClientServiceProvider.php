<?php

namespace Tochka\JsonRpcClient;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tochka\JsonRpcClient\Contracts\OnceExecutedMiddleware;
use Tochka\JsonRpcClient\Facades\JsonRpcClientMiddlewareRepository;
use Tochka\JsonRpcClient\Support\ClientConfig;
use Tochka\JsonRpcClient\Support\PsrHttpClient;
use Tochka\JsonRpcSupport\Middleware\MiddlewareRepository;

class JsonRpcClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/jsonrpc-client.php' => config_path('jsonrpc-client.php'),
            ],
            'jsonrpc-client-config'
        );
    }
    
    public function register(): void
    {
        $clients = Config::get('jsonrpc-client.connections', []);
        
        $this->app->singleton(Facades\PsrHttpClient::class, function () {
            $clientClassName = Config::get('jsonrpc-client.http.client', '\\GuzzleHttp\\Client');
            $requestFactoryClassName = Config::get(
                'jsonrpc-client.http.requestFactory',
                Psr17Factory::class
            );
            $streamFactoryClassName = Config::get(
                'jsonrpc-client.http.streamFactory',
                Psr17Factory::class
            );
            
            $client = $this->app->make($clientClassName);
            $requestFactory = $this->app->make($requestFactoryClassName);
            $streamFactory = $this->app->make($streamFactoryClassName);
            
            if (!$client instanceof ClientInterface || !$requestFactory instanceof RequestFactoryInterface || !$streamFactory instanceof StreamFactoryInterface) {
                throw new \RuntimeException(
                    'Some HTTP client instances for JsonRpcClient do not implement the required PSR interfaces'
                );
            }
            
            return new PsrHttpClient($client, $requestFactory, $streamFactory);
        });
        
        $this->app->singleton(JsonRpcClientMiddlewareRepository::class, function () {
            $manager = new MiddlewareRepository(Container::getInstance());
            
            $clients = Config::get('jsonrpc-client.connections', []);
            foreach ($clients as $clientName => $clientConfig) {
                $manager->parseMiddleware($clientName, $clientConfig['middleware'] ?? []);
            }
            
            return $manager;
        });
        
        foreach ($clients as $alias => $clientConfig) {
            $clientClass = $clientConfig['clientClass'] ?? null;
            
            if (class_exists($clientClass)) {
                $this->app->singleton($clientClass, function () use ($alias, $clientConfig) {
                    $config = new ClientConfig($alias, $clientConfig);
                    
                    $queryPreparer = $this->app->get($config->queryPreparer);
                    
                    return new JsonRpcClient($alias, $config, $queryPreparer, Container::getInstance());
                });
            }
        }
    }
}
